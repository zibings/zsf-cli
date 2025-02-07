<?php
	include './vendor/autoload.php';

	require_once 'SQLTable.php';

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
	$dbName = "stoic"; // TODO(Jovanni): Fix this should be zsf

	$templateData = [];

	try {
		$dsn = "$dbEngine:host=$dbHost;port=$dbPort;dbname=$dbName";
		echo($dsn . "\n");
		$pdo = new PDO($dsn, $dbUser, $dbPass);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$table = new SQLTable("User");
		$result = $table->parseTable($pdo);
		if ($result === false) {
			die("Failed to parse sql table");
		}

		$PrimaryKeyArgsWithoutTypes = [];
		for ($i = 0; $i < count($table->primaryKeys); $i++) {
			$PrimaryKeyArgsWithoutTypes[] = $table->primaryKeys[$i]->name;
		}

		$PrimaryKeyArgsWithTypes = [];
		for ($i = 0; $i < count($table->primaryKeys); $i++) {
			$PrimaryKeyArgsWithTypes[] = $table->primaryKeys[$i]->type . " $" . $table->primaryKeys[$i]->name;
		}

		$templateData = [
		  'ClassName' => $table->name,
		  'Columns' => $table->columns,
		  'PrimaryKeys' => $table->primaryKeys,
		  'FromPrimaryKey' => implode("_", $PrimaryKeyArgsWithoutTypes),
		  'PrimaryKeyArgs' => implode(",", $PrimaryKeyArgsWithoutTypes),
		  'PrimaryKeyArgsWithTypes' => implode(",", $PrimaryKeyArgsWithTypes),
			'UniqueKeys' => $table->uniqueKeys,
		];
	} catch (PDOException $e) {
		die("Database connection failed: " . $e->getMessage() . "\n");
	} finally {
		$pdo = null;
	}

	// Create a Plates engine

	$templatesDir = __DIR__ . '/templates';
	$engine = new League\Plates\Engine($templatesDir);
	$phpCode = $engine->render("cls", $templateData);

	$outputPath = __DIR__ . "/generated/".$templateData['ClassName'].".php";
	file_put_contents($outputPath, $phpCode);

