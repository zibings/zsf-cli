<?php

	namespace Zsf\Scripts;

	use AndyM84\Config\ConfigContainer;

	use Stoic\Utilities\ConsoleHelper;
	use Stoic\Utilities\FileHelper;

	use Zsf\Utils\ZsfCliScript;

	class GenerateScript implements ZsfCliScript {
		public function help() : string {
			return <<< HELP_TEXT
ZSF CLI Generate Script
-----------------------
Description:           Generate file(s) using meta data from existing db model(s)
Interactive Usage:     vendor/bin/zsf-cli generate
Non-Interactive Usage: vendor/bin/zsf-cli generate --type=js --model=model_name --out-dir=out_dir
                       vendor/bin/zsf-cli generate --type=ts --namespace=namespace --out-dir=out_dir
HELP_TEXT;
		}

		public function key() : string {
			return 'generate';
		}

		public function oneLineDescription() : string {
			return 'Generate file(s) using meta data  from existing db models';
		}

		public function run(ConsoleHelper $ch, FileHelper $fh, ConfigContainer $config) : void {
			$ch->putLine('ZSF Generate Script');
			$ch->putLine('-------------------');
			$ch->putLine();
			$ch->putLine('This script will generate file(s) from existing db model(s).');
			$ch->putLine('You can use the --model option to specify a specific model to generate.');
			$ch->putLine('You can use the --namespace option to specify the namespace for the generated files.');
			$ch->putLine('You can use the --out-dir option to specify the output directory for the generated files.');
			$ch->putLine();

			return;
		}
	}
