<?php

	namespace Zsf\Scripts;

	use AndyM84\Config\ConfigContainer;

	use Stoic\Utilities\ConsoleHelper;
	use Stoic\Utilities\FileHelper;

	use Zsf\Utils\ZsfCliScript;

	class VersionScript implements ZsfCliScript {
		public function help() : string {
			return "ZSF CLI Version Script" . PHP_EOL .
				"----------------------" . PHP_EOL .
				"Usage:       vendor/bin/zsf-cli version" . PHP_EOL .
				"Description: Display version information about installation" . PHP_EOL;
		}

		public function key() : string {
			return 'version';
		}

		public function oneLineDescription() : string {
			return 'Display version information about installation';
		}

		public function run(ConsoleHelper $ch, FileHelper $fh, ConfigContainer $config) : void {
			$ch->putLine('ZSF Version Information');
			$ch->putLine('-----------------------');
			$ch->putLine();

			if (defined('ZSF_VERSION')) {
				$ch->putLine('Current ZSF version: ' . ZSF_VERSION);
			} else {
				$ch->putLine('Current ZSF version: Unknown');
			}

			if ($config->has('systemVersion')) {
				$ch->putLine('System version:      ' . $config->get('systemVersion'));
			} else {
				$ch->putLine('System version:      Unknown');
			}

			$ch->putLine();

			if ($fh->fileExists('~/vendor/composer/installed.json')) {
				$installed = json_decode($fh->getContents('~/vendor/composer/installed.json'), true);

				if (is_array($installed)) {
					$ch->putLine('Installed composer packages:');

					$longest = 0;
					$pairs   = [];

					foreach ($installed['packages'] as $package) {
						$pairs[$package['name']] = $package['version'];
						$longest                 = max($longest, strlen($package['name']));
					}

					foreach ($pairs as $name => $version) {
						$ch->putLine('    ' . str_pad($name, $longest) . '  ' . $version);
					}
				}
			} else {
				$ch->putLine('Installed composer packages: Unknown / None found');
			}

			$ch->putLine();

			return;
		}
	}
