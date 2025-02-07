<?php

	namespace Zsf\Utils;

	use AndyM84\Config\ConfigContainer;

	use Stoic\Utilities\ConsoleHelper;
	use Stoic\Utilities\FileHelper;

	interface ZsfCliScript {
		public function help() : string;
		public function key() : string;
		public function oneLineDescription() : string;
		public function run(ConsoleHelper $ch, FileHelper $fh, ConfigContainer $config) : void;
	}
