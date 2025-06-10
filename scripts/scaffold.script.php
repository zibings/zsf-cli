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
				$input['interactive'],
				$input['namespace'],
				$input['overwrite'],
				$input['table'],
				$input['type'],
				$input['connection']
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
		 */
		public function __construct(
			public string $db,
			public bool $interactive,
			public string $namespace,
			public bool $overwrite,
			public string $table,
			public string $type,
			public null|string $connection
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
				'db'          => $ch->getParameterWithDefault('db', 'database', '', true),
				'interactive' => $ch->getParameterWithDefault('ni', 'non-interactive', false, true),
				'namespace'   => $ch->getParameterWithDefault('ns', 'namespace', '', true),
				'overwrite'   => !$ch->hasShortLongArg('no', 'no-overwrite', true),
				'table'       => $ch->getParameterWithDefault('table', 'table', '', true),
				'type'        => $ch->getParameterWithDefault('type', 'type', '', true),
				'connection'  => $ch->getParameterWithDefault('c', 'connection', null, true),
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

			if ($ret['interactive']) {
				if (!$validationFuncs['type']($ret['type'])) {
					$ch->putLine('Aborting script execution, invalid type specified. Valid types are: model, repo, api, all');

					exit;
				}

				if (empty($ret['db']) && empty($ret['table'])) {
					$ch->putLine('Aborting script execution, no source specified. Must include either `database` or `table` source name');

					exit;
				}

				if (!empty($ret['table']) && empty($ret['db'])) {
					$ch->putLine('Aborting script execution, no database specified. Must include a database name if using a table source');

					exit;
				}

				if (empty($ret['namespace'])) {
					$ch->putLine('Aborting script execution, no namespace specified');

					exit;
				}

				return ScaffoldArguments::fromArray($ret);
			}

			$maxTries = 3;

			$type = $ch->getQueriedInput(
				'What type of scaffolding do you want to generate?',
				'model, repo, api, all',
				'Invalid type specified. Valid types are: model, repo, api, all',
				$maxTries,
				$validationFuncs['type'],
				$sanitationFuncs['trimAndLower']
			);

			if ($type->isBad()) {
				$ch->putLine();
				$ch->putLine('Aborting script execution, invalid type specified. Valid types are: model, repo, api, all');

				exit;
			}

			$ret['type'] = $type->getResults()[0];

			$db = $ch->getQueriedInput(
				'Please enter a database name',
				null,
				'Invalid database name specified',
				$maxTries,
				$validationFuncs['empty'],
				$sanitationFuncs['trim']
			);

			if ($db->isBad()) {
				$ch->putLine();
				$ch->putLine('Aborting script execution, invalid database name specified');

				exit;
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
				$ch->putLine();
				$ch->putLine('Aborting script execution, invalid source specified. Valid sources are: database, table');

				exit;
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

			$connection = $ch->getQueriedInput(
				'Enter a db connection key',
				null,
				'Invalid connection key specified',
				1,
				function () { return true; },
				$sanitationFuncs['trimAndLower']
			);

			$ret['connection'] = $connection->getResults()[0];

			$namespace = $ch->getQueriedInput(
				'Please enter a namespace for the generated files',
				null,
				'Invalid namespace specified',
				$maxTries,
				$validationFuncs['empty'],
				$sanitationFuncs['trimAndLower']
			);

			if ($namespace->isBad()) {
				$ch->putLine();
				$ch->putLine('Aborting script execution, invalid namespace specified');

				exit;
			}

			$ret['namespace'] = $namespace->getResults()[0];

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
                       vendor/bin/zsf-cli scaffold --type=api --db=db_name --no-overwrite --namespace=namespace
                       vendor/bin/zsf-cli scaffold --type=all --db=db_name --namespace=namespace
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
			$ch->putLine('  Type:        ' . $input->type);
			$ch->putLine('  Table:       ' . $input->table ?? 'N/A');
			$ch->putLine('  Database:    ' . $input->db ?? 'N/A');
			$ch->putLine('  Namespace:   ' . $input->namespace);
			$ch->putLine('  Connection:  ' . $input->connection ?? 'N/A');
			$ch->putLine('  Overwrite:   ' . ($input->overwrite ? 'true' : 'false'));
			$ch->putLine('  Interactive: ' . (!$input->interactive ? 'true' : 'false'));
			$ch->putLine();

			$db = $this->__getDb($input, $ch, $config);

			if ($db->isActive() === false) {
				$ch->putLine('Aborting script execution, unable to connect to database');

				return;
			}

			$ch->putLine('Database connection established');
			$ch->putLine('  DSN:         ' . $db->dsn);

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
					$fileCreatePaths['api'] = $fh->pathJoin($baseCreatePath, 'api', 'generated');

					break;
				case 'all':
					$fileCreatePaths['cls'] = $fh->pathJoin($baseCreatePath, $config->get('classesPath'), 'generated');
					$fileCreatePaths['rpo'] = $fh->pathJoin($baseCreatePath, $config->get('reposPath'), 'generated');
					$fileCreatePaths['api'] = $fh->pathJoin($baseCreatePath, 'api', 'generated');

					break;
				default:
					break;
			}

			if (count($fileCreatePaths) === 0) {
				$ch->putLine('Aborting script execution, no file paths to create');

				return;
			}

			foreach ($fileCreatePaths as $type => $path) {
				if (!$fh->folderExists($path)) {
					$fh->makeFolder($path, 0755, true);
					$ch->putLine('Created directory: ' . $path);
				}
			}

			try {
				$reader = new $readerDriver($db);

				if (!empty($input->table)) {
					$reader->parseTableColumns($input->table, $input->db);
				} else {
					$reader->parseAllTableColumns($input->db);
				}

				$ch->putLine();

				foreach ($reader->tables as $table => $columns) {
					$ch->putLine('Generating Table Data: ' . $table);

					$columnArgsStrings          = [];
					$primaryKeys                = [];
					$primaryKeyArgsStrings      = [];
					$primaryKeyArgsWithTypes    = [];
					$primaryKeyArgsWithoutTypes = [];

					foreach ($columns as $column) {
						$columnArgsStrings[] = "'" . $column->getName() . "'";

						foreach ($column->getFlags() as $flag) {
							if ($flag->is(BaseDbColumnFlags::IS_KEY)) {
								$primaryKeys[]                = $column;
								$primaryKeyArgsWithoutTypes[] = $column->getName();
								$primaryKeyArgsStrings[]      = "'" . $column->getName() . "'";
								$primaryKeyArgsWithTypes[]    = $column->getPhpType() . " $" . $column->getName();
							}
						}
					}

					$tplData = [
						'Namespace'               => $input->namespace,
						'ClassName'               => $table,
						'Columns'                 => $columns,
						'ColumnArgsStrings'       => implode(", ", $columnArgsStrings),
						'PrimaryKeys'             => $primaryKeys,
						'PrimaryKeyArgsStrings'   => implode(", ", $primaryKeyArgsStrings),
						'FromPrimaryKey'          => implode("_", $primaryKeyArgsWithoutTypes),
						'PrimaryKeyArgs'          => implode(", ", $primaryKeyArgsWithoutTypes),
						'PrimaryKeyArgsWithTypes' => implode(", ", $primaryKeyArgsWithTypes),
					];

					foreach ($fileCreatePaths as $type => $path) {
						$ch->putLine('  Generating ' . $type . ' file...');

						$engine     = new \League\Plates\Engine($fh->pathJoin('~/templates'));
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
