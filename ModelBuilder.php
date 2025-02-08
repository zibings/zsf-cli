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

		$table = new SQLTable("LoginKey");
		$result = $table->parseTable($pdo);
		if ($result === false) {
			die("Failed to parse sql table");
		}

		$PrimaryKeyArgsWithoutTypes = [];
		$PrimaryKeyArgsWithTypes = [];
		$PrimaryKeyArgsStrings = [];
		for ($i = 0; $i < count($table->primaryKeys); $i++) {
			$PrimaryKeyArgsWithoutTypes[] = $table->primaryKeys[$i]->name;
			$PrimaryKeyArgsWithTypes[] = $table->primaryKeys[$i]->type . " $" . $table->primaryKeys[$i]->name;
			$PrimaryKeyArgsStrings[] = "'" . $table->primaryKeys[$i]->name . "'";
		}

		$UniqueKeyArgsWithoutTypes = [];
		$UniqueKeyArgsWithTypes = [];
		for ($i = 0; $i < count($table->uniqueKeys); $i++) {
			$UniqueKeyArgsWithoutTypes[] = $table->uniqueKeys[$i]->name;
			$UniqueKeyArgsWithTypes[] = $table->uniqueKeys[$i]->type . " $" . $table->uniqueKeys[$i]->name;
		}

		$templateData = [
		  'ClassName' => $table->name,
		  'Columns' => $table->columns,

			'PrimaryKeys' => $table->primaryKeys,
			'PrimaryKeyArgsStrings' => implode(", ", $PrimaryKeyArgsStrings),
			'FromPrimaryKey' => implode("_", $PrimaryKeyArgsWithoutTypes),
		  'PrimaryKeyArgs' => implode(", ", $PrimaryKeyArgsWithoutTypes),
		  'PrimaryKeyArgsWithTypes' => implode(", ", $PrimaryKeyArgsWithTypes),

			'UniqueKeys' => $table->uniqueKeys,
			'FromUniqueKey' => implode("_", $UniqueKeyArgsWithoutTypes),
			'UniqueKeyArgsWithoutTypes' => implode(", ", $UniqueKeyArgsWithoutTypes),
			'UniqueKeyArgsWithTypes' => implode(", ", $UniqueKeyArgsWithTypes),
			'UniqueKeyArgs' => implode(", ", $UniqueKeyArgsWithoutTypes),
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
	$outputPath = __DIR__ . "/generated/cls/" . $templateData['ClassName'] . ".cls.php";
	file_put_contents($outputPath, $phpCode);

	$phpCode = $engine->render("api", $templateData);
	$outputPath = __DIR__ . "/generated/api/" . $templateData['ClassName'] . ".api.php";
	file_put_contents($outputPath, $phpCode);

	// $phpCode = $engine->render("rpo", $templateData);
	// $outputPath = __DIR__ . "/generated/".$templateData['ClassName'].".php";
	// file_put_contents($outputPath, $phpCode);

