<?php
	function databaseToPhpType($dbType): string {
		$dbType = strtolower($dbType);
		if (str_contains($dbType, 'int') || str_contains($dbType, 'tinyint') || str_contains($dbType, 'bigint')) {
			return 'int';
		} else if (str_contains($dbType, 'float') || str_contains($dbType, 'double') || str_contains($dbType, 'decimal')) {
			return 'float';
		} else if (str_contains($dbType, 'varchar') || str_contains($dbType, 'text') || str_contains($dbType, 'char') || str_contains($dbType, 'mediumtext') || strpos($dbType, 'longtext') !== false) {
			return 'string';
		} else if (str_contains($dbType, 'date') || str_contains($dbType, 'datetime') || str_contains($dbType, 'timestamp')) {
			return 'DateTime';
		} else if (str_contains($dbType, 'bool')) {
			return 'bool';
		} else {
			return '???'; // Default
		}
	}

	class SQLColumn {
		public string $name;
		public string $type;
		public bool $isNull;
		public bool $isKey;
		public bool $isUnique;
		public string $extra;

		public function __construct(string $name, string $type) {
			$this->name = $name;
			$this->type = $type;
		}
	}


	class SqlTable {
		public string $name = "";

		/**
		 * @var SQLColumn[] An array of SQLColumn objects.
		 */
		public array $columns = [];

		public function __construct() {}
		public function parseTable(PDO $pdo) {

		}
	}