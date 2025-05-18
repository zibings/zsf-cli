<?php

	namespace Zsf\Utils\SchemaReader;

	use Stoic\Log\Logger;
	use Stoic\Pdo\BaseDbColumnFlags;
	use Stoic\Pdo\BaseDbTypes;
	use Stoic\Pdo\PdoDrivers;
	use Stoic\Pdo\PdoHelper;

	class SqlServerColumn extends ISchemaColumn {
		/**
		 * Returns all appropriate flags for the column.
		 *
		 * @return BaseDbColumnFlags[]
		 */
		public function getFlags() : array {
			return $this->flags;
		}

		/**
		 * Returns the name of the column.
		 *
		 * @return string
		 */
		public function getName() : string {
			return $this->data['name'] ?? '!!ERROR!!';
		}

		/**
		 * Returns the Stoic base model type for the column.
		 *
		 * @return BaseDbTypes
		 */
		public function getModelType() : BaseDbTypes {
			return $this->modelType;
		}

		/**
		 * Returns the PHP type for the column.
		 *
		 * @return string
		 */
		public function getPhpType() : string {
			return $this->phpType;
		}

		/**
		 * Internal method to parse the column data.
		 *
		 * @return void
		 */
		protected function parseColumn() : void {
			if (isset($this->data['type'])) {
				$this->modelType = match (strtolower($this->data['type'])) {
					'int', 'tinyint', 'smallint', 'bigint' => new BaseDbTypes(BaseDbTypes::INTEGER),
					'date', 'datetime', 'datetime2', 'datetimeoffset', 'smalldatetime' => new BaseDbTypes(BaseDbTypes::DATETIME),
					'bit' => new BaseDbTypes(BaseDbTypes::BOOLEAN),
					default => new BaseDbTypes(BaseDbTypes::STRING),
				};

				$this->phpType = match (strtolower($this->data['type'])) {
					'int', 'tinyint', 'smallint', 'bigint' => 'int',
					'float', 'real', 'decimal', 'money', 'smallmoney' => 'float',
					'date', 'datetime', 'datetime2', 'datetimeoffset', 'smalldatetime' => '\DateTimeInterface',
					'bit' => 'bool',
					default => 'string',
				};
			}

			if (isset($this->data['key']) && $this->data['key'] === 'PK') {
				$this->flags[] = new BaseDbColumnFlags(BaseDbColumnFlags::IS_KEY);
			}

			if (isset($this->data['nullable']) && $this->data['nullable'] === 'NO') {
				$this->flags[] = new BaseDbColumnFlags(BaseDbColumnFlags::ALLOWS_NULLS);
			}

			if (isset($this->data['is_identity']) && $this->data['is_identity'] === 'YES') {
				$this->flags[] = new BaseDbColumnFlags(BaseDbColumnFlags::AUTO_INCREMENT);
			} else {
				$this->flags[] = new BaseDbColumnFlags(BaseDbColumnFlags::SHOULD_INSERT);
				$this->flags[] = new BaseDbColumnFlags(BaseDbColumnFlags::SHOULD_UPDATE);
			}

			return;
		}
	}

	/**
	 * Class for reading Microsoft SQL Server schema information.
	 *
	 * @package Zsf\Utils\SchemaReader
	 */
	class SqlServer extends ISchemaReader {
		/**
		 * Instantiates a new SQL Server schema reader object.
		 *
		 * @param PdoHelper $db
		 * @param Logger|null $log
		 * @throws \ReflectionException
		 */
		public function __construct(PdoHelper $db, ?Logger $log = null) {
			parent::__construct($db, $log);

			$this->setDriver(PdoDrivers::PDO_SQLSRV);

			return;
		}

		/**
		 * Fetches all columns for any tables found in the schema and parses their data.
		 *
		 * @param string $schemaName
		 * @return void
		 */
		public function parseAllTableColumns(string $schemaName) : void {
			if (!$this->db->isActive()) {
				return;
			}

			$columns = [];

			try {
				$stmt = $this->db->prepare("
                SELECT 
                    t.name AS [TABLE_NAME],
                    c.name AS [COLUMN_NAME],
                    ty.name AS [DATA_TYPE],
                    CASE 
                        WHEN pk.column_id IS NOT NULL THEN 'PK' 
                        ELSE '' 
                    END AS [COLUMN_KEY],
                    CASE 
                        WHEN c.is_nullable = 0 THEN 'NO' 
                        ELSE 'YES' 
                    END AS [IS_NULLABLE],
                    CASE 
                        WHEN c.is_identity = 1 THEN 'YES' 
                        ELSE 'NO' 
                    END AS [IS_IDENTITY],
                    c.default_object_id AS [DEFAULT_OBJECT_ID]
                FROM 
                    sys.tables t
                INNER JOIN 
                    sys.columns c ON t.object_id = c.object_id
                INNER JOIN 
                    sys.types ty ON c.user_type_id = ty.user_type_id
                INNER JOIN 
                    sys.schemas s ON t.schema_id = s.schema_id
                LEFT JOIN (
                    SELECT 
                        ic.column_id, 
                        ic.object_id
                    FROM 
                        sys.index_columns ic
                    INNER JOIN 
                        sys.indexes i ON ic.object_id = i.object_id AND ic.index_id = i.index_id
                    WHERE 
                        i.is_primary_key = 1
                ) pk ON c.object_id = pk.object_id AND c.column_id = pk.column_id
                WHERE 
                    s.name = :schemaName
                ORDER BY 
                    t.name, c.column_id
            ");

				$stmt->bindValue(':schemaName', $schemaName);

				if ($stmt->execute() && $stmt->rowCount() > 0) {
					while ($tableRow = $stmt->fetch(\PDO::FETCH_ASSOC)) {
						$table = $tableRow['TABLE_NAME'];

						if (array_key_exists($table, $columns) === false) {
							$columns[$table] = [];
						}

						$columns[$table][] = new SqlServerColumn([
							'name'        => $tableRow['COLUMN_NAME'],
							'type'        => $tableRow['DATA_TYPE'],
							'key'         => $tableRow['COLUMN_KEY'],
							'nullable'    => $tableRow['IS_NULLABLE'],
							'is_identity' => $tableRow['IS_IDENTITY']
						]);
					}

					$this->tables = $columns;
				}
			} catch (\PDOException $e) {
				$this->log->error("Failed to fetch columns for schema '{$schemaName}': " . $e->getMessage());
			}

			return;
		}

		/**
		 * Fetches the columns for the specified table and parses its data.
		 *
		 * @param string $tableName
		 * @param string $schemaName
		 * @return void
		 */
		public function parseTableColumns(string $tableName, string $schemaName = 'dbo') : void {
			if (!$this->db->isActive()) {
				return;
			}

			$columns = [];

			try {
				$sql = "
                SELECT 
                    c.name AS [COLUMN_NAME],
                    ty.name AS [DATA_TYPE],
                    CASE 
                        WHEN pk.column_id IS NOT NULL THEN 'PK' 
                        ELSE '' 
                    END AS [COLUMN_KEY],
                    CASE 
                        WHEN c.is_nullable = 0 THEN 'NO' 
                        ELSE 'YES' 
                    END AS [IS_NULLABLE],
                    CASE 
                        WHEN c.is_identity = 1 THEN 'YES' 
                        ELSE 'NO' 
                    END AS [IS_IDENTITY],
                    c.default_object_id AS [DEFAULT_OBJECT_ID]
                FROM 
                    sys.columns c
                INNER JOIN 
                    sys.tables t ON c.object_id = t.object_id
                INNER JOIN 
                    sys.schemas s ON t.schema_id = s.schema_id
                INNER JOIN 
                    sys.types ty ON c.user_type_id = ty.user_type_id
                LEFT JOIN (
                    SELECT 
                        ic.column_id, 
                        ic.object_id
                    FROM 
                        sys.index_columns ic
                    INNER JOIN 
                        sys.indexes i ON ic.object_id = i.object_id AND ic.index_id = i.index_id
                    WHERE 
                        i.is_primary_key = 1
                ) pk ON c.object_id = pk.object_id AND c.column_id = pk.column_id
                WHERE 
                    t.name = :tableName
                    AND s.name = :schemaName
                ORDER BY 
                    c.column_id
            ";

				$stmt = $this->db->prepare($sql);
				$stmt->bindValue(':tableName', $tableName);
				$stmt->bindValue(':schemaName', $schemaName === '' ? 'dbo' : $schemaName);

				if ($stmt->execute() && $stmt->rowCount() > 0) {
					while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
						$columns[] = new SqlServerColumn([
							'name'        => $row['COLUMN_NAME'],
							'type'        => $row['DATA_TYPE'],
							'key'         => $row['COLUMN_KEY'],
							'nullable'    => $row['IS_NULLABLE'],
							'is_identity' => $row['IS_IDENTITY']
						]);
					}
				}

				if (count($columns) > 0) {
					$this->tables[$tableName] = $columns;
				}
			} catch (\PDOException $e) {
				$this->log->error("Failed to fetch columns for table '{$tableName}': " . $e->getMessage());
			}

			return;
		}
	}
