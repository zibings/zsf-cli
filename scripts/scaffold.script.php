<?php

	namespace Zsf\Scripts;

	use AndyM84\Config\ConfigContainer;

	use Stoic\Pdo\PdoHelper;
	use Stoic\Utilities\ConsoleHelper;
	use Stoic\Utilities\FileHelper;

	use Zsf\Utils\ZsfCliScript;

	class ScaffoldArguments {
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


		public function __construct(
			public string $db,
			public bool $interactive,
			public string $namespace,
			public bool $overwrite,
			public string $table,
			public string $type,
			public string $connection
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

			$key = $args->connection ?? 'default';

			foreach ($config->getSettings() as $key => $value) {
				if (str_starts_with($key, 'dbDsns.') === false) {
					continue;
				}

				$dbSetKey = str_replace('dbDsns.', '', $key);

				if ($dbSetKey !== $key) {
					continue;
				}

				return new PdoHelper(
					$value,
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
				'overwrite'   => $ch->hasShortLongArg('no', 'no-overwrite', true),
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
				'trimAndLower' => function (mixed $value) : string {
					return trim($value);
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

			$source = $ch->getQueriedInput(
				'Would you like to generate from a database or a table?',
				'database, table',
				'Invalid source specified. Valid sources are: database, table',
				$maxTries,
				$validationFuncs['source'],
				$sanitationFuncs['trimAndLower']
			);

			if ($source->isBad()) {
				$ch->putLine();
				$ch->putLine('Aborting script execution, invalid source specified. Valid sources are: database, table');

				exit;
			}

			$source = $source->getResults()[0];

			if ($source == 'table') {
				$table = $ch->getQueriedInput(
					'Please enter a table name',
					null,
					'Invalid table name specified',
					$maxTries,
					$validationFuncs['empty'],
					$sanitationFuncs['trimAndLower']
				);

				if ($table->isBad()) {
					$ch->putLine();
					$ch->putLine('Aborting script execution, invalid table name specified');

					exit;
				}

				$ret['table'] = $table->getResults()[0];
			} else {
				$db = $ch->getQueriedInput(
					'Please enter a database name',
					null,
					'Invalid database name specified',
					$maxTries,
					$validationFuncs['empty'],
					$sanitationFuncs['trimAndLower']
				);

				if ($db->isBad()) {
					$ch->putLine();
					$ch->putLine('Aborting script execution, invalid database name specified');

					exit;
				}

				$ret['db'] = $db->getResults()[0];
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
				'yes, no',
				'Invalid input specified. Valid inputs are: yes, no',
				$maxTries,
				$validationFuncs['yesno'],
				$sanitationFuncs['trimAndLower']
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
Description:           Generate scaffold file(s) from a database or database tables
Interactive Usage:     vendor/bin/zsf-cli scaffold
Non-Interactive Usage: vendor/bin/zsf-cli scaffold --type=model --table=table_name --namespace=namespace
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

			$input = $this->__getInput($ch);

			$ch->putLine('Input:');
			$ch->putLine('  Type:        ' . $input['type']);
			$ch->putLine('  Table:       ' . $input['table']);
			$ch->putLine('  Database:    ' . $input['db']);
			$ch->putLine('  Namespace:   ' . $input['namespace']);
			$ch->putLine('  Connection:  ' . $input['connection']);
			$ch->putLine('  Overwrite:   ' . ($input['overwrite'] ? 'true' : 'false'));
			$ch->putLine('  Interactive: ' . ($input['interactive'] ? 'true' : 'false'));
			$ch->putLine();

			return;
		}
	}
