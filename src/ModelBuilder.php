<?php
	// Generate cls
	// Generate rpo
	// Generate api

	// Database connection here
	$siteSettings = [
	  'dbEngine' => '',
	  'dbHost' => '',
	  'dbPort' => -1,
	  'dbUser' => '',
	  'dbPass' => '',
	  'dbName' => '',
	];

	// Function to safely get settings (with default values or error handling)
	function getSetting(array $settings, string $key, $defaultValue = null) {
		if (array_key_exists($key, $settings)) {
			return $settings[$key];
		}
		return $defaultValue; // Or throw an exception if you prefer
	}

	//$dbEngine = getSetting($siteSettings, 'dbEngine');
	//$dbHost   = getSetting($siteSettings, 'dbHost');
	//$dbPort   = getSetting($siteSettings, 'dbPort');
	//$dbUser   = getSetting($siteSettings, 'dbUser');
	//$dbPass   = getSetting($siteSettings, 'dbPass');
	//$dbName   = getSetting($siteSettings, 'dbName');

	$dbEngine = "mysql";
	$dbHost = "localhost";
	$dbPort = 3306;
	$dbUser = "root";
	$dbPass = "P@55word";
	$dbName = "zsf";

	$templateData = [];

	try {
		$dsn = "$dbEngine:host=$dbHost;port=$dbPort;dbname=$dbName";
		echo($dsn . "\n");
		$pdo = new PDO($dsn, $dbUser, $dbPass);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$table = new SQLTable();
		$result = $table->parseTable($pdo);
		if ($result === false) {
			die("Failed to parse sql table");
		}

		$templateData = [
		  'ClassName' => $table->name, // PHP class name
		  'Columns' => $table->columns,
		  'PrimaryKeys' => $table->primaryKeys,
		  'PrimaryKeyArgs' => implode(",", $table->primaryKeys),
		  'UniqueKeys' => $table->uniqueKeys,
		];
	} catch (PDOException $e) {
		die("Database connection failed: " . $e->getMessage() . "\n");
	} finally {
		$pdo = null;
	}

	// Create a Plates engine
	$engine = new League\Plates\Engine('/templates/cls.tpl');
	$phpCode = $engine->render('php_class', $templateData);
	file_put_contents("./generated/".$templateData['ClassName'].".php", $phpCode);

