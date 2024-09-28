<?php
	require_once 'MySQLSchemaReader.php';
	require_once 'ClassGenerator.php';

	function main() {
		$config = [
			'user' => 'root',
			'password' => 'P@55word',
			'host' => 'localhost',
			'database' => 'PGM'
		];

		$db_name = 'PGM';

		// Initialize and connect to the MySQL schema reader
		$schema_reader = new MySQLSchemaReader($config, $db_name);
		$schema_reader->connect();
		$columns = $schema_reader->fetchColumns();
		$schema_reader->disconnect();

		// Initialize PHP class generator and create class files for each table
		$generator = new ClassGenerator();
		foreach ($columns as $table_name => $column_list) {
			$generator->generateClass($table_name, $column_list);
		}

		echo "PHP class files generated successfully.\n";
	}

	// Only call main if this script is run directly
	if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
		main();
	}