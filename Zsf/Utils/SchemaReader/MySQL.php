<?php

	namespace Zsf\Utils\SchemaReader;

	use Stoic\Log\Logger;
	use Stoic\Pdo\BaseDbColumnFlags;
	use Stoic\Pdo\BaseDbTypes;
	use Stoic\Pdo\PdoDrivers;
	use Stoic\Pdo\PdoHelper;

	use Zsf\Utils\Translators\MySQLTranslator;

	class MySQLColumn extends ISchemaColumn {
		/**
		 * Returns all appropriate flags for the column.
		 *
		 * @return BaseDbColumnFlags[]
		 */
		public function getFlags() : array {
			return $this->flags;
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
			$translator = new MySQLTranslator();

			if (isset($this->data['type'])) {
				$this->modelType = match ($this->data['type']) {
					'int',     'tinyint',  'bigint'    => new BaseDbTypes(BaseDbTypes::INTEGER),
					'date',    'datetime', 'timestamp' => new BaseDbTypes(BaseDbTypes::DATETIME),
					'bool',    'boolean'               => new BaseDbTypes(BaseDbTypes::BOOLEAN),
					default                            => new BaseDbTypes(BaseDbTypes::STRING),
				};

				$this->phpType = $translator->toPhpType($this->data['type']);
			}

			if (isset($this->data['key']) && $this->data['key'] === 'PRI') {
				$this->flags[] = new BaseDbColumnFlags(BaseDbColumnFlags::IS_KEY);
			}

			if (isset($this->data['nullable']) && $this->data['nullable'] !== 'NO') {
				$this->flags[] = new BaseDbColumnFlags(BaseDbColumnFlags::ALLOWS_NULLS);
			}

			if (isset($this->data['extra']) && str_contains($this->data['extra'], 'auto_increment')) {
				$this->flags[] = new BaseDbColumnFlags(BaseDbColumnFlags::AUTO_INCREMENT);
			} else {
				$this->flags[] = new BaseDbColumnFlags(BaseDbColumnFlags::SHOULD_INSERT);
				$this->flags[] = new BaseDbColumnFlags(BaseDbColumnFlags::SHOULD_UPDATE);
			}

			return;
		}
	}

	/**
	 * Class for reading MySQL schema information.
	 *
	 * @package Zsf\Utils\SchemaReader
	 */
	class MySQL extends ISchemaReader {
		/**
		 * Instantiates a new MySQL schema reader object.
		 *
		 * @param PdoHelper $db
		 * @param Logger|null $log
		 * @throws \ReflectionException
		 */
		public function __construct(PdoHelper $db, ?Logger $log = null) {
			parent::__construct($db, $log);

			$this->setDriver(PdoDrivers::PDO_MYSQL);

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
				$stmt = $this->db->prepare("SELECT `TABLE_NAME`, `COLUMN_NAME`, `DATA_TYPE`, `COLUMN_KEY`, `IS_NULLABLE`, `EXTRA` FROM INFORMATION_SCHEMA.COLUMNS WHERE `TABLE_SCHEMA` = :schemaName ORDER BY `TABLE_NAME`");
				$stmt->bindValue(':schemaName', $schemaName);

				if ($stmt->execute() && $stmt->rowCount() > 0) {
					while ($tableRow = $stmt->fetch(\PDO::FETCH_ASSOC)) {
						$table = $tableRow['TABLE_NAME'];

						if (array_key_exists($table, $columns) === false) {
							$columns[$table] = [];
						}

						$columns[$table][] = new MySQLColumn([
							'name'     => $tableRow['COLUMN_NAME'],
							'type'     => $tableRow['DATA_TYPE'],
							'key'      => $tableRow['COLUMN_KEY'],
							'nullable' => $tableRow['IS_NULLABLE'],
							'extra'    => $tableRow['EXTRA']
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
		public function parseTableColumns(string $tableName, string $schemaName = '') : void {
			if (!$this->db->isActive()) {
				return;
			}

			$columns = [];

			try {
				$sql = "SELECT `COLUMN_NAME`, `DATA_TYPE`, `COLUMN_KEY`, `IS_NULLABLE`, `EXTRA` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_NAME` = :tableName";

				if ($schemaName !== '') {
					$sql .= " AND `TABLE_SCHEMA` = :schemaName";
				}

				$stmt = $this->db->prepare($sql);
				$stmt->bindValue(':tableName', $tableName);

				if ($schemaName !== '') {
					$stmt->bindValue(':schemaName', $schemaName);
				}

				if ($stmt->execute() && $stmt->rowCount() > 0) {
					while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
						$columns[] = new MySQLColumn([
							'name'     => $row['COLUMN_NAME'],
							'type'     => $row['DATA_TYPE'],
							'key'      => $row['COLUMN_KEY'],
							'nullable' => $row['IS_NULLABLE'],
							'extra'    => $row['EXTRA']
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
