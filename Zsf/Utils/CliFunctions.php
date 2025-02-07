<?php

	namespace Zsf\Utils;

	/**
	 * Determines whether the given filename is being called directly from the command line.
	 *
	 * @param string $fileName
	 * @return bool
	 */
	function isRunningFile(string $fileName) : bool {
		return basename($fileName) === basename($_SERVER['SCRIPT_FILENAME']);
	}
