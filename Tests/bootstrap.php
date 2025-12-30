<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 'On');
	setlocale(LC_ALL, 'en_US.utf8');
	date_default_timezone_set('America/New_York');

	if (extension_loaded('xdebug')) {
		echo("XDebug extension loaded and running\n");

		xdebug_enable();
	} else {
		echo("XDebug extension not found, please configure and retry\n");
	}

	require('vendor/autoload.php');
