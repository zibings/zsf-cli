<?php

	namespace Zsf\Utils\SchemaReader;

	use Stoic\Log\Logger;
	use Stoic\Pdo\PdoDrivers;
	use Stoic\Pdo\PdoHelper;

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
		 * Fetches the columns for the specified database.
		 *
		 * @param string $dbName
		 * @return array
		 */
		public function fetchColumns(string $dbName) : array {
			return [];
		}

		/**
		 * Fetches all columns for any tables found in the schema.
		 *
		 * @param string $schemaName
		 * @return array
		 */
		public function fetchAllTableColumns(string $schemaName) : array {
			if (!$this->db->isActive()) {
				return [];
			}

			$columns      = [];
			$currentTable = null;

			try {
				$tblStmt = $this->db->prepare("SELECT `TABLE_NAME`, `COLUMN_NAME`, `DATA_TYPE`, `COLUMN_KEY`, `IS_NULLABLE`, `EXTRA` FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :schemaName");
				$tblStmt->bindValue(':schemaName', $schemaName);

				if ($tblStmt->execute() && $tblStmt->rowCount() > 0) {
					while ($tableRow = $tblStmt->fetch(\PDO::FETCH_ASSOC)) {
						if ($tableRow['TABLE_NAME'] !== $currentTable) {
							$currentTable           = $tableRow['TABLE_NAME'];
							$columns[$currentTable] = [];
						}

						$columns[$currentTable][] = [
							'name'     => $tableRow['COLUMN_NAME'],
							'type'     => $tableRow['DATA_TYPE'],
							'key'      => $tableRow['COLUMN_KEY'],
							'nullable' => $tableRow['IS_NULLABLE'],
							'extra'    => $tableRow['EXTRA']
						];

						$this->log->info(sprintf(
							"%-12s | %-12s | %-10s | %-8s | %-4s | %s",
							$tableRow['TABLE_NAME'],
							$tableRow['COLUMN_NAME'],
							$tableRow['DATA_TYPE'],
							$tableRow['IS_NULLABLE'],
							$tableRow['COLUMN_KEY'],
							$tableRow['EXTRA']
						));
					}
				}
			} catch (\PDOException $e) {
				$this->log->error("Failed to fetch columns for schema '{$schemaName}': " . $e->getMessage());
			}

			return $columns;
		}
	}
