<?php
	// Generate cls
	// Generate rpo
	// Generate api
	require 'vendor/autoload.php'; // For Plates (if you want HTML output of the form)
	// ... (database connection code as before)
	// ... (code to fetch column and constraint information from the database as before)
	// Data Preparation for Template (PHP)

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

	$dbEngine = getSetting($siteSettings, 'dbEngine');
	$dbHost   = getSetting($siteSettings, 'dbHost');
	$dbPort   = getSetting($siteSettings, 'dbPort');
	$dbUser   = getSetting($siteSettings, 'dbUser');
	$dbPass   = getSetting($siteSettings, 'dbPass');
	$dbName   = getSetting($siteSettings, 'dbName');

	$templateData = [];

	try {
		$dsn = "$dbEngine:host=$dbHost;port=$dbPort;dbname=$dbName";
		$pdo = new PDO($dsn, $dbUser, $dbPass);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$table = new SQLTable($pdo);
		$templateData = [
		  'ClassName' => $table->name, // PHP class name
		  'PrimaryKeys' => $table->primaryKeys,
		  'PrimaryKeyArgs' => implode(",", $table->primaryKeys),
		  'UniqueKeys' => $table->uniqueKeys,
		  'Extras' => $table->extras,
		];

	} catch (PDOException $e) {
		echo "Database connection failed: " . $e->getMessage() . "\n";
		// Handle the error appropriately (log it, display a message, etc.)
	} finally {
		// Close the connection (important!)
		$pdo = null; // Setting $pdo to null closes the connection
	}





	// Create a Plates engine
	$engine = new League\Plates\Engine('./templates');
	$phpCode = $engine->render('php_class', $templateData);
	file_put_contents("./generated/".$templateData['ClassName'].".php", $phpCode);

