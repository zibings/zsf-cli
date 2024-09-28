<?php
	class MySQLSchemaReader {
		private $config;
		private $db_name;
		private $conn;

		public function __construct($config, $db_name) {
			$this->config = $config;
			$this->db_name = $db_name;
			$this->conn = null;
		}

		public function connect() {
			try {
				$dsn = "mysql:host={$this->config['host']};dbname={$this->db_name};charset=utf8mb4";
				$this->conn = new PDO($dsn, $this->config['user'], $this->config['password']);
				$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch (PDOException $e) {
				die("Connection failed: " . $e->getMessage());
			}
		}

		public function disconnect() {
			$this->conn = null;
		}

		public function fetchColumns() {
			$query = "
        SELECT 
            TABLE_NAME,
            COLUMN_NAME, 
            DATA_TYPE, 
            COLUMN_KEY, 
            IS_NULLABLE,
            EXTRA
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = :db_name;
        ";

			try {
				$stmt = $this->conn->prepare($query);
				$stmt->bindParam(':db_name', $this->db_name);
				$stmt->execute();
				$columns = [];
				$current_table = null;

				echo "Column Name  | Data Type  | Nullable  | Key  | Extra\n";
				echo "---------------------------------------------------\n";
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					if ($row['TABLE_NAME'] !== $current_table) {
						$current_table = $row['TABLE_NAME'];
						$columns[$current_table] = [];
					}

					$columns[$current_table][] = [
						'name' => $row['COLUMN_NAME'],
						'type' => $row['DATA_TYPE'],
						'key' => $row['COLUMN_KEY'],
						'nullable' => $row['IS_NULLABLE'],
						'extra' => $row['EXTRA']
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
				return $columns;
			} catch (PDOException $e) {
				die("Error fetching columns: " . $e->getMessage());
			}
		}
	}
