<?php

	namespace Zsf\Scripts;

	use AndyM84\Config\ConfigContainer;

	use Stoic\Utilities\ConsoleHelper;
	use Stoic\Utilities\FileHelper;

	use Zsf\Utils\ZsfCliScript;

	class ScaffoldScript implements ZsfCliScript {
		public function __getInput(ConsoleHelper $ch) : array {
			$ret = [
				'db'          => $ch->getParameterWithDefault('db', 'database', '', true),
				'interactive' => $ch->getParameterWithDefault('ni', 'non-interactive', false, true),
				'namespace'   => $ch->getParameterWithDefault('ns', 'namespace', '', true),
				'overwrite'   => $ch->getParameterWithDefault('no', 'no-overwrite', false, true),
				'table'       => $ch->getParameterWithDefault('table', 'table', '', true),
				'type'        => $ch->getParameterWithDefault('type', 'type', '', true),
			];

			$validationFuncs = [
				'empty' => function (mixed $value) : bool {
					return !empty($value);
				},
				'type' => function (mixed $value) : bool {
					return !empty($value) && in_array(strtolower($value), ['model', 'repo', 'api', 'all']);
				},
			];

			if ($ret['interactive']) {
				if (!$validationFuncs['type']($ret['type'])) {
					$ch->putLine('Aborting script execution, invalid type specified. Valid types are: model, repo, api, all');

					exit;
				}

				return $ret;
			}

			$type = $ch->getQueriedInput(
				'What type of scaffolding do you want to generate?',
				'model, repo, api, all',
				'Invalid type specified. Valid types are: model, repo, api, all',
				5,
				$validationFuncs['type'],
				function ($value) {
					return trim(strtolower($value));
				}
			);

			if ($type->isBad()) {
				$ch->putLine();
				$ch->putLine('Aborting script execution, invalid type specified. Valid types are: model, repo, api, all');

				exit;
			}

			return $ret;
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

			return;
		}
	}
