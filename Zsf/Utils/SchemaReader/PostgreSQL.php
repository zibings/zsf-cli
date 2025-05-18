<?php

	namespace Zsf\Utils\SchemaReader;

	use Stoic\Log\Logger;
	use Stoic\Pdo\BaseDbColumnFlags;
	use Stoic\Pdo\BaseDbTypes;
	use Stoic\Pdo\PdoDrivers;
	use Stoic\Pdo\PdoHelper;

	class PostgresColumn extends ISchemaColumn {
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
				$this->modelType = match ($this->data['type']) {
					'integer', 'smallint', 'bigint'       => new BaseDbTypes(BaseDbTypes::INTEGER),
					'date', 'timestamp', 'timestamptz'    => new BaseDbTypes(BaseDbTypes::DATETIME),
					'boolean'                             => new BaseDbTypes(BaseDbTypes::BOOLEAN),
					default                               => new BaseDbTypes(BaseDbTypes::STRING),
				};

				$this->phpType = match ($this->data['type']) {
					'integer', 'smallint', 'bigint'       => 'int',
					'numeric', 'real', 'double precision' => 'float',
					'date', 'timestamp', 'timestamptz'    => '\DateTimeInterface',
					'boolean'                             => 'bool',
					default                               => 'string',
				};
			}

			if (isset($this->data['key']) && $this->data['key'] === 'PK') {
				$this->flags[] = new BaseDbColumnFlags(BaseDbColumnFlags::IS_KEY);
			}

			if (isset($this->data['nullable']) && $this->data['nullable'] === 'NO') {
				$this->flags[] = new BaseDbColumnFlags(BaseDbColumnFlags::ALLOWS_NULLS);
			}

			if (isset($this->data['default']) && str_contains($this->data['default'], 'nextval')) {
				$this->flags[] = new BaseDbColumnFlags(BaseDbColumnFlags::AUTO_INCREMENT);
			} else {
				$this->flags[] = new BaseDbColumnFlags(BaseDbColumnFlags::SHOULD_INSERT);
				$this->flags[] = new BaseDbColumnFlags(BaseDbColumnFlags::SHOULD_UPDATE);
			}

			return;
		}
	}

	/**
	 * Class for reading PostgreSQL schema information.
	 *
	 * @package Zsf\Utils\SchemaReader
	 */
	class Postgres extends ISchemaReader {
		/**
		 * Instantiates a new PostgreSQL schema reader object.
		 *
		 * @param PdoHelper $db
		 * @param Logger|null $log
		 * @throws \ReflectionException
		 */
		public function __construct(PdoHelper $db, ?Logger $log = null) {
			parent::__construct($db, $log);

			$this->setDriver(PdoDrivers::PDO_PGSQL);

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
                    t.tablename AS \"TABLE_NAME\",
                    a.attname AS \"COLUMN_NAME\",
                    pg_catalog.format_type(a.atttypid, a.atttypmod) AS \"DATA_TYPE\",
                    CASE 
                        WHEN co.contype = 'p' THEN 'PK' 
                        ELSE '' 
                    END AS \"COLUMN_KEY\",
                    CASE 
                        WHEN a.attnotnull THEN 'NO' 
                        ELSE 'YES' 
                    END AS \"IS_NULLABLE\",
                    pg_catalog.pg_get_expr(ad.adbin, ad.adrelid) AS \"DEFAULT\"
                FROM 
                    pg_catalog.pg_tables t
                JOIN 
                    pg_catalog.pg_class c ON t.tablename = c.relname
                JOIN 
                    pg_catalog.pg_namespace n ON t.schemaname = n.nspname AND c.relnamespace = n.oid
                JOIN 
                    pg_catalog.pg_attribute a ON a.attrelid = c.oid
                LEFT JOIN 
                    pg_catalog.pg_constraint co ON co.conrelid = c.oid AND a.attnum = ANY(co.conkey)
                LEFT JOIN 
                    pg_catalog.pg_attrdef ad ON ad.adrelid = c.oid AND ad.adnum = a.attnum
                WHERE 
                    t.schemaname = :schemaName
                    AND a.attnum > 0
                    AND NOT a.attisdropped
                ORDER BY 
                    t.tablename, a.attnum
            ");

				$stmt->bindValue(':schemaName', $schemaName);

				if ($stmt->execute() && $stmt->rowCount() > 0) {
					while ($tableRow = $stmt->fetch(\PDO::FETCH_ASSOC)) {
						$table = $tableRow['TABLE_NAME'];

						if (array_key_exists($table, $columns) === false) {
							$columns[$table] = [];
						}

						$columns[$table][] = new PostgresColumn([
							'name'     => $tableRow['COLUMN_NAME'],
							'type'     => $tableRow['DATA_TYPE'],
							'key'      => $tableRow['COLUMN_KEY'],
							'nullable' => $tableRow['IS_NULLABLE'],
							'default'  => $tableRow['DEFAULT'] ?? ''
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
		public function parseTableColumns(string $tableName, string $schemaName = 'public') : void {
			if (!$this->db->isActive()) {
				return;
			}

			$columns = [];

			try {
				$sql = "
                SELECT 
                    a.attname AS \"COLUMN_NAME\",
                    pg_catalog.format_type(a.atttypid, a.atttypmod) AS \"DATA_TYPE\",
                    CASE 
                        WHEN co.contype = 'p' THEN 'PK' 
                        ELSE '' 
                    END AS \"COLUMN_KEY\",
                    CASE 
                        WHEN a.attnotnull THEN 'NO' 
                        ELSE 'YES' 
                    END AS \"IS_NULLABLE\",
                    pg_catalog.pg_get_expr(ad.adbin, ad.adrelid) AS \"DEFAULT\"
                FROM 
                    pg_catalog.pg_attribute a
                JOIN 
                    pg_catalog.pg_class c ON a.attrelid = c.oid
                JOIN 
                    pg_catalog.pg_namespace n ON c.relnamespace = n.oid
                LEFT JOIN 
                    pg_catalog.pg_constraint co ON co.conrelid = c.oid AND a.attnum = ANY(co.conkey)
                LEFT JOIN 
                    pg_catalog.pg_attrdef ad ON ad.adrelid = c.oid AND ad.adnum = a.attnum
                WHERE 
                    c.relname = :tableName
                    AND n.nspname = :schemaName
                    AND a.attnum > 0
                    AND NOT a.attisdropped
                ORDER BY
                    a.attnum
            ";

				$stmt = $this->db->prepare($sql);
				$stmt->bindValue(':tableName', $tableName);
				$stmt->bindValue(':schemaName', $schemaName === '' ? 'public' : $schemaName);

				if ($stmt->execute() && $stmt->rowCount() > 0) {
					while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
						$columns[] = new PostgresColumn([
							'name'     => $row['COLUMN_NAME'],
							'type'     => $row['DATA_TYPE'],
							'key'      => $row['COLUMN_KEY'],
							'nullable' => $row['IS_NULLABLE'],
							'default'  => $row['DEFAULT'] ?? ''
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
