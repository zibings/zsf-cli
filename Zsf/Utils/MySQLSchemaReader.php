<?php

	namespace Zsf\Utils;

	/**
	 * Class for reading MySQL schema information.
	 *
	 * @package Zsf\Utils
	 */
	class MySQLSchemaReader {
		private array $config;
		private string $dbName;
		private null|\PDO $conn = null;


		/**
		 * Instantiates a new MySQLSchemaReader object.
		 *
		 * @param array $config
		 * @param string $dbName
		 */
		public function __construct(array $config, string $dbName) {
			$this->config = $config;
			$this->dbName = $dbName;

			return;
		}

		public function connect() : void {
			try {
				$dsn        = "mysql:host={$this->config['host']};dbname={$this->dbName};charset=utf8mb4";
				$this->conn = new \PDO($dsn, $this->config['user'], $this->config['password']);

				$this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			} catch (\PDOException $e) {
				die("Connection failed: " . $e->getMessage());
			}

			return;
		}

		public function disconnect() : void {
			$this->conn = null;

			return;
		}

		public function fetchColumns() : array {
			$query = "
        SELECT 
            TABLE_NAME,
            COLUMN_NAME, 
            DATA_TYPE, 
            COLUMN_KEY, 
            IS_NULLABLE,
            EXTRA
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = :dbName;
        ";

			$columns      = [];
			$currentTable = null;

			try {
				$stmt = $this->conn->prepare($query);
				$stmt->bindParam(':dbName', $this->dbName);
				$stmt->execute();

				echo "Column Name  | Data Type  | Nullable  | Key  | Extra\n";
				echo "---------------------------------------------------\n";

				while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
					if ($row['TABLE_NAME'] !== $currentTable) {
						$currentTable           = $row['TABLE_NAME'];
						$columns[$currentTable] = [];
					}

					$columns[$currentTable][] = [
						'name'     => $row['COLUMN_NAME'],
						'type'     => $row['DATA_TYPE'],
						'key'      => $row['COLUMN_KEY'],
						'nullable' => $row['IS_NULLABLE'],
						'extra'    => $row['EXTRA']
					];

					echo sprintf(
						"%-12s | %-12s | %-10s | %-8s | %-4s | %s\n",
						$row['TABLE_NAME'],
						$row['COLUMN_NAME'],
						$row['DATA_TYPE'],
						$row['IS_NULLABLE'],
						$row['COLUMN_KEY'],
						$row['EXTRA']
					);
				}
			} catch (\PDOException $e) {
				die("Error fetching columns: " . $e->getMessage());
			}

			return $columns;
		}
	}
