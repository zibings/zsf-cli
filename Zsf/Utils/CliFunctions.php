<?php

	namespace Zsf\Utils;

	function isRunningFile(string $fileName) : bool {
		return basename($fileName) === basename($_SERVER['SCRIPT_FILENAME']);
	}
