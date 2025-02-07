<?php

	function databaseToPhpType(string $dbType): string {
		$dbType = strtolower($dbType);
		if (str_contains($dbType, 'int') || str_contains($dbType, 'tinyint') || str_contains($dbType, 'bigint')) {
			return 'int';
		} else if (str_contains($dbType, 'float') || str_contains($dbType, 'double') || str_contains($dbType, 'decimal')) {
			return 'float';
		} else if (str_contains($dbType, 'varchar') || str_contains($dbType, 'text') || str_contains($dbType, 'char') || str_contains($dbType, 'mediumtext') || strpos($dbType, 'longtext') !== false) {
			return 'string';
		} else if (str_contains($dbType, 'date') || str_contains($dbType, 'datetime') || str_contains($dbType, 'timestamp')) {
			return 'DateTime'; // Or string if you prefer to keep it as a string
		} else if (str_contains($dbType, 'bool')) {
			return 'bool';
		} else {
			return 'mixed'; // Or throw an exception for unknown types if you prefer
		}
	}

	class FlagTypes {
		const int IS_KEY = 1;
		const int IS_NULL = 2;
		const int IS_UNIQUE = 4;
		const int IS_AUTO_INCREMENT = 8; // Add a flag for auto-increment

		public static function generateFlags(string $key, string $null, string $extra): int {
			$flags = 0;
			if ($key === 'PRI') {
				$flags |= self::IS_KEY;
			}
			if ($null === 'NO') {
				$flags |= self::IS_NULL;
			}
			if ($key === 'UNI') {
				$flags |= self::IS_UNIQUE;
			}
			if (str_contains($extra, 'auto_increment')) {
				$flags |= self::IS_AUTO_INCREMENT;
			}
			return $flags;
		}
	}

	class SQLColumn {
		public string $name;
		public string $type;
		public int $flags;
		public string $extra;

		public function __construct(string $name, string $type, int $flags, string $extra) {
			$this->name = $name;
			$this->type = $type;
			$this->flags = $flags;
			$this->extra = $extra;
		}
	}

	class SqlTable {
		public string $name = "";

		/**
		 * @var SQLColumn[] An array of SQLColumn objects.
		 */
		public array $columns = [];

		/**
		 * @var SQLColumn[] An array of SQLColumn objects representing primary keys.
		 */
		public array $primaryKeys = [];

		/**
		 * @var SQLColumn[] An array of SQLColumn objects representing unique keys.
		 */
		public array $uniqueKeys = [];


		public function __construct(string $name) {
			$this->name = $name;
		}

		/**
		 * @param \PDO $pdo valid database connection
		 * @return bool returns false is parsing fails
		 */
		public function parseTable(PDO $pdo): bool {
			try {
				$sql = "DESCRIBE " . $this->name;
				$stmt = $pdo->query($sql);
				if ($stmt === false) {
					return false;
				}

				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$flags = FlagTypes::generateFlags($row['Key'], $row['Null'], $row['Extra']);
					$type = databaseToPhpType($row['Type']);
					$column = new SQLColumn($row['Field'], $type, $flags, $row['Extra']);
					$this->columns[] = $column;

					if ($row['Key'] === 'PRI') {
						$this->primaryKeys[] = $column;
					} else if ($row['Key'] === 'UNI') {
						$this->uniqueKeys[] = $column;
					}
				}
				return true;
			} catch (PDOException $e) {
				echo "Error parsing table: " . $e->getMessage();
				return false;
			}
		}

		public function __toString(): string {
			$output = "Table: " . $this->name . "\n";

			$output .= "Primary Keys:\n";
			foreach ($this->primaryKeys as $pk) {
				$output .= "  " . $pk->name . " (" . $pk->type . ")\n";
			}

			$output .= "Unique Keys:\n";
			foreach ($this->uniqueKeys as $uk) {
				$output .= "  " . $uk->name . " (" . $uk->type . ")\n";
			}

			$output .= "Columns:\n";
			foreach ($this->columns as $column) {
				$flagsString = [];
				if ($column->flags & FlagTypes::IS_KEY) $flagsString[] = "KEY";
				if ($column->flags & FlagTypes::IS_NULL) $flagsString[] = "NULL";
				if ($column->flags & FlagTypes::IS_UNIQUE) $flagsString[] = "UNIQUE";
				if ($column->flags & FlagTypes::IS_AUTO_INCREMENT) $flagsString[] = "AUTO_INCREMENT";

				$output .= "  " . $column->name . " (" . $column->type . ") Flags: [" . implode(", ", $flagsString) . "] Extra: " . $column->extra . "\n";
			}
			return $output;
		}
	}