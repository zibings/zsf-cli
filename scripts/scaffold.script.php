<?php

	namespace Zsf\Scripts;

	use AndyM84\Config\ConfigContainer;

	use Stoic\Pdo\BaseDbColumnFlags;
	use Stoic\Pdo\PdoDrivers;
	use Stoic\Pdo\PdoHelper;
	use Stoic\Utilities\ConsoleHelper;
	use Stoic\Utilities\FileHelper;

	use Zsf\Utils\SchemaReader\MySQL as MySQLReader;
	use Zsf\Utils\SchemaReader\Postgres as PostgresReader;
	use Zsf\Utils\SchemaReader\SqlServer as SqlServerReader;
	use Zsf\Utils\ZsfCliScript;

	class ScaffoldArguments {
		/**
		 * Create a ScaffoldArguments object from an array of input values.
		 *
		 * @param array $input
		 * @return ScaffoldArguments
		 */
		public static function fromArray(array $input) : ScaffoldArguments {
			return new ScaffoldArguments(
				$input['db'],
				$input['namespace'],
				$input['overwrite'] ?? false,
				$input['table'],
				$input['type'],
				$input['connection'],
				$input['camelCase'] ?? false,
				$input['apiNamespace'] ?? '',
			);
		}


		/**
		 * Instantiates a ScaffoldArguments object with the provided parameters.
		 *
		 * @param string $db
		 * @param bool $interactive
		 * @param string $namespace
		 * @param bool $overwrite
		 * @param string $table
		 * @param string $type
		 * @param null|string $connection
		 * @param bool $camelCase
		 */
		public function __construct(
			public string $db,
			public string $namespace,
			public bool $overwrite,
			public string $table,
			public string $type,
			public null|string $connection,
			public bool $camelCase = false,
			public string $apiNamespace = '',
		) {
			return;
		}
	}

	class ScaffoldScript implements ZsfCliScript {
		public function __getDb(ScaffoldArguments $args, ConsoleHelper $ch, ConfigContainer $config) : PdoHelper {
			if ($config->has('dbDsn')) {
				$dsn = $config->get('dbDsn');
				$user = $config->get('dbUser');
				$pass = $config->get('dbPass');

				// modify the following line to have exceptions
				return new PdoHelper(
					$dsn,
					$user,
					$pass,
					[
						\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
						\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
						\PDO::ATTR_EMULATE_PREPARES   => false,
					]
				);
			}

			$key = $args->connection;

			if (empty($key)) {
				$key = 'default';
			}

			foreach ($config->getSettings() as $settingsKey => $settingsValue) {
				if (str_starts_with($settingsKey, 'dbDsns.') === false) {
					continue;
				}

				$dbSetKey = str_replace('dbDsns.', '', $settingsKey);

				if ($dbSetKey !== $key) {
					continue;
				}

				return new PdoHelper(
					$settingsValue,
					$config->get('dbUsers.' . $dbSetKey),
					$config->get('dbPasses.' . $dbSetKey),
					[
						\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
						\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
						\PDO::ATTR_EMULATE_PREPARES   => false,
					]
				);
			}

			$ch->putLine('Aborting script execution, no connection information found for key: ' . $key);

			exit;
		}

		public function __getInput(ConsoleHelper $ch) : ScaffoldArguments {
			$ret = [
				'db'           => $ch->getParameterWithDefault('db', 'database', '', true),
				'namespace'    => $ch->getParameterWithDefault('ns', 'namespace', '', true),
				'table'        => $ch->getParameterWithDefault('table', 'table', '', true),
				'type'         => $ch->getParameterWithDefault('type', 'type', '', true),
				'connection'   => $ch->getParameterWithDefault('c', 'connection', null, true),
				'apiNamespace' => $ch->getParameterWithDefault('api', 'api-namespace', '', true),
				'overwrite'    => $ch->hasShortLongArg('ow', 'overwrite', true) ? true : null,
				'camelCase'    => $ch->hasShortLongArg('camel', 'camel-case', true) ? true : null,
			];

			$validationFuncs = [
				'empty'  => function (mixed $value) : bool {
					return !empty($value);
				},
				'type'   => function (mixed $value) : bool {
					return in_array(strtolower($value), ['model', 'repo', 'api', 'all']);
				},
				'source' => function (mixed $value) : bool {
					return in_array(strtolower($value), ['database', 'table']);
				},
				'yesno'  => function (mixed $value) : bool {
					return in_array(strtolower($value), ['yes', 'no', 'y', 'n']);
				}
			];

			$sanitationFuncs = [
				'trim'         => function (mixed $value) : string {
					return trim($value);
				},
				'trimAndLower' => function (mixed $value) : string {
					return strtolower(trim($value));
				},
				'yesno'        => function (mixed $value) : string {
					$value = strtolower(trim($value));

					if ($value == 'yes' || $value == 'y') {
						return 'yes';
					}

					return 'no';
				}
			];

			$maxTries       = 3;
			$wasInteractive = false;
			$requiredArgs   = [
				'namespace'    => [
					'error'      => 'Namespace is required for scaffolding operations',
					'query'      => function () use (&$ret, $maxTries, $validationFuncs, $sanitationFuncs, $ch) {
						$namespace = $ch->getQueriedInput(
							'Please enter a namespace for the generated files',
							null,
							'Invalid namespace specified',
							$maxTries,
							$validationFuncs['empty'],
							$sanitationFuncs['trim']
						);

						if ($namespace->isBad()) {
							return false;
						}

						$ret['namespace'] = $namespace->getResults()[0];

						return true;
					},
					'validation' => function (array $args) use ($validationFuncs) {
						return !empty($args['namespace']);
					}
				],
				'type'         => [
					'error'      => 'Type is required for scaffolding operations',
					'query'      => function () use (&$ret, $maxTries, $validationFuncs, $sanitationFuncs, $ch) {
						$type = $ch->getQueriedInput(
							'What type of scaffolding do you want to generate?',
							'model, repo, api, all',
							'Invalid type specified. Valid types are: model, repo, api, all',
							$maxTries,
							$validationFuncs['type'],
							$sanitationFuncs['trimAndLower']
						);

						if ($type->isBad()) {
							return false;
						}

						$ret['type'] = $type->getResults()[0];

						return true;
					},
					'validation' => function (array $args) use ($validationFuncs) {
						return $validationFuncs['type']($args['type']);
					}
				],
				'connection'   => [
					'error'      => 'Database connection key and name are required for scaffolding operations',
					'query'      => function () use (&$ret, $maxTries, $validationFuncs, $sanitationFuncs, $ch) {
						$connection = $ch->getQueriedInput(
							'Enter a db connection key',
							null,
							'Invalid connection key specified',
							1,
							function () { return true; },
							$sanitationFuncs['trimAndLower']
						);

						if ($connection->isBad()) {
							return false;
						}

						$ret['connection'] = $connection->getResults()[0];

						return true;
					},
					'validation' => function (array $args) use ($validationFuncs) {
						return !empty($args['connection']);
					}
				],
				'source'       => [
					'error'      => 'Either a database or table name is required for scaffolding operations',
					'query'      => function () use (&$ret, $maxTries, $validationFuncs, $sanitationFuncs, $ch) {
						$db = $ch->getQueriedInput(
							'Please enter a database name',
							null,
							'Invalid database name specified',
							$maxTries,
							$validationFuncs['empty'],
							$sanitationFuncs['trim']
						);

						if ($db->isBad()) {
							return false;
						}

						$ret['db'] = $db->getResults()[0];

						$source = $ch->getQueriedInput(
							'Would you like to generate from a specific table?',
							'(y)es, (n)o',
							'Invalid input specified. Valid inputs are: yes, no, y, or n',
							$maxTries,
							$validationFuncs['yesno'],
							$sanitationFuncs['yesno']
						);

						if ($source->isBad()) {
							return false;
						}

						$source = $source->getResults()[0];

						if ($source == 'yes') {
							$table = $ch->getQueriedInput(
								'Please enter a table name',
								null,
								'Invalid table name specified',
								$maxTries,
								$validationFuncs['empty'],
								$sanitationFuncs['trim']
							);

							if ($table->isBad()) {
								$ch->putLine();
								$ch->putLine('Aborting script execution, invalid table name specified');

								exit;
							}

							$ret['table'] = $table->getResults()[0];
						}

						return true;
					},
					'validation' => function (array $args) use ($validationFuncs) {
						if (empty($args['db']) && empty($args['table'])) {
							return false;
						}

						if (!empty($args['table']) && empty($args['db'])) {
							return false;
						}

						return true;
					}
				],
				'apiNamespace' => [
					'error'      => 'API Namespace is required for API scaffolding operations',
					'query'      => function () use (&$ret, $maxTries, $validationFuncs, $sanitationFuncs, $ch) {
						$apiNamespace = $ch->getQueriedInput(
							'Enter a namespace for the generated API files',
							null,
							'Invalid namespace specified',
							$maxTries,
							$validationFuncs['empty'],
							$sanitationFuncs['trim']
						);

						if ($apiNamespace->isBad()) {
							return false;
						}

						$ret['apiNamespace'] = $apiNamespace->getResults()[0];

						return true;
					},
					'validation' => function (array $args) use ($validationFuncs) {
						if ($args['type'] == 'api' || $args['type'] == 'all') {
							return !empty($args['apiNamespace']);
						}

						return true;
					}
				]
			];

			foreach ($requiredArgs as $argValue) {
				if (!$argValue['validation']($ret)) {
					$wasInteractive = true;

					if (!$argValue['query']()) {
						$ch->putLine();
						$ch->putLine("Aborting script execution, " . $argValue['error']);
					}
				}
			}

			if (!$wasInteractive) {
				return ScaffoldArguments::fromArray($ret);
			}

			if (!isset($ret['overwrite'])) {
				$overwrite = $ch->getQueriedInput(
					'Would you like to overwrite existing files?',
					'(y)es, (n)o',
					'Invalid input specified. Valid inputs are: yes, no, y, or n',
					$maxTries,
					$validationFuncs['yesno'],
					$sanitationFuncs['yesno']
				);

				if ($overwrite->isBad()) {
					$ch->putLine();
					$ch->putLine('Aborting script execution, invalid input specified. Valid inputs are: yes, no');

					exit;
				}

				$ret['overwrite'] = $overwrite->getResults()[0] == 'yes' || $overwrite->getResults()[0] == 'y';
			}

			if (!isset($ret['camelCase'])) {
				$camelCase = $ch->getQueriedInput(
					'Would you like to output properties in camelCase?',
					'(y)es, (n)o',
					'Invalid input specified. Valid inputs are: yes, no, y, or n',
					$maxTries,
					$validationFuncs['yesno'],
					$sanitationFuncs['yesno']
				);

				if ($camelCase->isBad()) {
					$ch->putLine();
					$ch->putLine('Aborting script execution, invalid input specified. Valid inputs are: yes, no');

					exit;
				}

				$ret['camelCase'] = $camelCase->getResults()[0] == 'yes' || $camelCase->getResults()[0] == 'y';
			}

			return ScaffoldArguments::fromArray($ret);
		}

		public function help() : string {
			return <<< HELP_TEXT
ZSF CLI Scaffolding Script
--------------------------
{$this->helpInternal()}
HELP_TEXT;
		}

		public function helpInternal() : string {
			return <<< HELP_TEXT
Description:           Generate scaffold file(s) from a database or database tables
Interactive Usage:     vendor/bin/zsf-cli scaffold
Non-Interactive Usage: vendor/bin/zsf-cli scaffold --type=model --db=db_name --table=table_name --namespace=namespace
                       vendor/bin/zsf-cli scaffold --type=repo --db=db_name --namespace=namespace
                       vendor/bin/zsf-cli scaffold --type=api --db=db_name --overwrite --namespace=namespace
                       vendor/bin/zsf-cli scaffold --type=all --db=db_name --namespace=namespace --camel-case
HELP_TEXT;
		}

		public function key() : string {
			return 'scaffold';
		}

		public function oneLineDescription() : string {
			return 'Generate scaffold file(s) from a database or database tables';
		}

		public function run(ConsoleHelper $ch, FileHelper $fh, ConfigContainer $config) : void {
			$ch->putLine('ZSF Scaffolding Script');
			$ch->putLine('----------------------');
			$ch->putLine();

			if ($ch->hasShortLongArg('help', 'h')) {
				$ch->putLine($this->helpInternal());

				return;
			}

			$input = $this->__getInput($ch);

			$ch->putLine('Input:');
			$ch->putLine('  Type:          ' . $input->type);
			$ch->putLine('  Table:         ' . $input->table ?? 'N/A');
			$ch->putLine('  Database:      ' . $input->db ?? 'N/A');
			$ch->putLine('  Namespace:     ' . $input->namespace);
			$ch->putLine('  API Namespace: ' . $input->apiNamespace);
			$ch->putLine('  Connection:    ' . $input->connection ?? 'N/A');
			$ch->putLine('  Overwrite:     ' . ($input->overwrite ? 'true' : 'false'));
			$ch->putLine();

			$db = $this->__getDb($input, $ch, $config);

			if ($db->isActive() === false) {
				$ch->putLine('Aborting script execution, unable to connect to database');

				return;
			}

			$ch->putLine('Database connection established');
			$ch->putLine('  DSN:         ' . $db->dsn);
			$ch->putLine();

			$readerDriver = match ($db->getDriver()->getValue()) {
				PdoDrivers::PDO_MSSQL, PdoDrivers::PDO_SQLSRV => SqlServerReader::class,
				PdoDrivers::PDO_MYSQL => MySQLReader::class,
				PdoDrivers::PDO_PGSQL => PostgresReader::class,
				default => null,
			};

			if ($readerDriver === null) {
				$ch->putLine();
				$ch->putLine('Aborting script execution, unsupported database driver: ' . $db->getDriver()->jsonSerialize());
				$ch->putLine();

				return;
			}

			$fileCreatePaths = [];
			$baseCreatePath  = $fh->pathJoin($config->get('includePath'));

			switch ($input->type) {
				case 'model':
					$fileCreatePaths['cls'] = $fh->pathJoin($baseCreatePath, $config->get('classesPath'), 'generated');

					break;
				case 'repo':
					$fileCreatePaths['rpo'] = $fh->pathJoin($baseCreatePath, $config->get('reposPath'), 'generated');

					break;
				case 'api':
					$fileCreatePaths['api'] = $fh->pathJoin('~/api', 'generated');

					break;
				case 'all':
					$fileCreatePaths['cls'] = $fh->pathJoin($baseCreatePath, $config->get('classesPath'), 'generated');
					$fileCreatePaths['rpo'] = $fh->pathJoin($baseCreatePath, $config->get('reposPath'), 'generated');
					$fileCreatePaths['api'] = $fh->pathJoin('~/api', 'generated');

					break;
				default:
					break;
			}

			if (count($fileCreatePaths) === 0) {
				$ch->putLine('Aborting script execution, no file paths to create');

				return;
			}

			$directoriesCreated = false;

			foreach ($fileCreatePaths as $path) {
				if (!$fh->folderExists($path)) {
					$fh->makeFolder($path, 0755, true);
					$ch->putLine('Created directory: ' . $path);

					$directoriesCreated = true;
				}
			}

			if ($directoriesCreated) {
				$ch->putLine();
			}

			try {
				$reader = new $readerDriver($db);

				if (!empty($input->table)) {
					$reader->parseTableColumns($input->table, $input->db);
				} else {
					$reader->parseAllTableColumns($input->db);
				}

				foreach ($reader->tables as $table => $columns) {
					$ch->putLine('Generating Table Data: ' . $table);

					$widestColumnNameLength     = 0;
					$widestPrimaryKeyNameLength = 0;
					$columnArgsStrings          = [];
					$primaryKeys                = [];
					$primaryKeyArgsStrings      = [];
					$primaryKeyArgsWithTypes    = [];
					$primaryKeyArgsWithoutTypes = [];

					foreach ($columns as $column) {
						$colNameLength       = strlen($column->getName($input->camelCase));
						$columnArgsStrings[] = "'" . $column->getName($input->camelCase) . "'";

						if ($colNameLength > $widestColumnNameLength) {
							$widestColumnNameLength = $colNameLength;
						}

						foreach ($column->getFlags() as $flag) {
							if ($flag->is(BaseDbColumnFlags::IS_KEY)) {
								if ($colNameLength > $widestPrimaryKeyNameLength) {
									$widestPrimaryKeyNameLength = $colNameLength;
								}

								$primaryKeys[]                = $column;
								$primaryKeyArgsWithoutTypes[] = $column->getName($input->camelCase);
								$primaryKeyArgsStrings[]      = "'" . $column->getName($input->camelCase) . "'";
								$primaryKeyArgsWithTypes[]    = $column->getPhpType() . " $" . $column->getName($input->camelCase);
							}
						}
					}

					$tplData = [
						'CamelCase'                  => $input->camelCase,
						'Namespace'                  => $input->namespace,
						'ApiNamespace'               => $input->apiNamespace,
						'ClassName'                  => $table,
						'Columns'                    => $columns,
						'WidestColumnNameLength'     => $widestColumnNameLength,
						'ColumnArgsStrings'          => implode(", ", $columnArgsStrings),
						'PrimaryKeys'                => $primaryKeys,
						'WidestPrimaryKeyNameLength' => $widestPrimaryKeyNameLength,
						'PrimaryKeyArgsStrings'      => implode(", ", $primaryKeyArgsStrings),
						'FromPrimaryKey'             => implode("_", $primaryKeyArgsWithoutTypes),
						'PrimaryKeyArgs'             => implode(", ", $primaryKeyArgsWithoutTypes),
						'PrimaryKeyArgsWithTypes'    => implode(", ", $primaryKeyArgsWithTypes),
					];

					$tplRootPath = '~/templates/scaffold';

					if (!$fh->folderExists($tplRootPath)) {
						$tplRootPath = '~/vendor/zibings/zsf-cli/templates/scaffold';

						if (!$fh->folderExists($tplRootPath)) {
							$ch->putLine('Aborting script execution, scaffold template folder not found');

							exit;
						}
					}

					foreach ($fileCreatePaths as $type => $path) {
						$ch->putLine('  Generating ' . $type . ' file...');

						$engine     = new \League\Plates\Engine($fh->pathJoin($tplRootPath), 'tpl');
						$phpCode    = $engine->render($type, $tplData);
						$outputPath = $fh->pathJoin($path, $table . '.' . $type . '.php');

						if ($input->overwrite || !$fh->fileExists($outputPath)) {
							$fh->putContents($outputPath, $phpCode);
							$ch->putLine('    File created: ' . $outputPath);
						} else {
							$ch->putLine('    File already exists, skipping: ' . $outputPath);
						}
					}
				}
			} catch (\Exception $e) {
				$ch->putLine('Error: ' . $e->getMessage());

				exit;
			}

			return;
		}
	}
