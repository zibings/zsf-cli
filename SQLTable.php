<?php

	function databaseToPhpType(string $dbType): string {
		$dbType = strtolower($dbType);
		$typeLookup = [
			'int'        => 'int',
			'tinyint'    => 'int',
			'bigint'     => 'int',
			'float'      => 'float',
			'double'     => 'float',
			'decimal'    => 'float',
			'varchar'    => 'string',
			'text'       => 'string',
			'char'       => 'string',
			'mediumtext' => 'string',
			'longtext'   => 'string',
			'date'       => '\DateTimeInterface',
			'datetime'   => '\DateTimeInterface',
			'timestamp'  => '\DateTimeInterface',
			'bool'       => 'bool',
		];

		foreach ($typeLookup as $key => $value) {
			if (str_contains($dbType, $key)) {
				return $value;
			}
		}

		return '???';
	}

	function typeToBaseDbTypes(string $type): string {
		$typeLookup = [
			'int'                => 'BaseDbTypes::INTEGER',
			'float'              => 'BaseDbTypes::FLOAT',
			'string'             => 'BaseDbTypes::STRING',
			'\DateTimeInterface' => 'BaseDbTypes::DATETIME',
			'bool'               => 'BaseDbTypes::BOOLEAN',
		];

		foreach ($typeLookup as $key => $value) {
			if (str_contains($type, $key)) {
				return $value;
			}
		}

		return '???';
	}

	class FlagTypes {
		const int IS_KEY = 1;
		const int SHOULD_INSERT = 2;
		const int SHOULD_UPDATE = 4;
		const int ALLOWS_NULLS = 8;
		const int IS_UNIQUE = 16;
		const int AUTO_INCREMENT = 32; // Add a flag for auto-increment

		public static function generateFlags(string $key, string $null, string $extra): int {
			$flags = 0;
			if ($key === 'PRI') {
				$flags |= self::IS_KEY;
			}
			if ($null === 'NO') {
				$flags |= self::ALLOWS_NULLS;
			}
			if ($key === 'UNI') {
				$flags |= self::IS_UNIQUE;
			}
			if (str_contains($extra, 'auto_increment')) {
				$flags |= self::AUTO_INCREMENT;
			}
			return $flags;
		}
	}

	class SQLColumn {
		public string $name;
		public string $type;
		public string $baseType;
		public int $flags;
		public string $flagsToString;
		public string $extra;

		public function __construct(string $name, string $type, string $baseType, int $flags, string $extra) {
			$this->name = $name;
			$this->type = $type;
			$this->baseType = $baseType;
			$this->flags = $flags;
			$this->extra = $extra;
		}

		public function finalizeFlags() {
			$finalizedArray = [];
			if ($this->flags & FlagTypes::IS_KEY) {
				$finalizedArray[] = "BaseDbTypes::IS_KEY";
			}
			if ($this->flags & FlagTypes::SHOULD_INSERT) {
				$finalizedArray[] = "BaseDbTypes::SHOULD_INSERT";
			}
			if ($this->flags & FlagTypes::IS_UNIQUE) {
				$finalizedArray[] = "BaseDbTypes::IS_UNIQUE";
			}
			if ($this->flags & FlagTypes::SHOULD_UPDATE) {
				$finalizedArray[] = "BaseDbTypes::SHOULD_UPDATE";
			}
			if ($this->flags & FlagTypes::ALLOWS_NULLS) {
				$finalizedArray[] = "BaseDbTypes::ALLOWS_NULLS";
			}
			if ($this->flags & FlagTypes::AUTO_INCREMENT) {
				$finalizedArray[] = "BaseDbTypes::AUTO_INCREMENT";
			}

			$this->flagsToString = implode(" | ", $finalizedArray);
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
					$baseType = typeToBaseDbTypes($type);
					$column = new SQLColumn($row['Field'], $type, $baseType, $flags, $row['Extra']);
					$column->finalizeFlags();
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
				if ($column->flags & FlagTypes::ALLOWS_NULLS) $flagsString[] = "NULL";
				if ($column->flags & FlagTypes::IS_UNIQUE) $flagsString[] = "UNIQUE";
				if ($column->flags & FlagTypes::AUTO_INCREMENT) $flagsString[] = "AUTO_INCREMENT";

				$output .= "  " . $column->name . " (" . $column->type . ") Flags: [" . implode(", ", $flagsString) . "] Extra: " . $column->extra . "\n";
			}
			return $output;
		}
	}