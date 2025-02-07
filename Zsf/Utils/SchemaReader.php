<?php

	namespace Zsf\Utils;

	use Stoic\Log\Logger;
	use Stoic\Pdo\PdoDrivers;
	use Stoic\Pdo\PdoHelper;

	use Zsf\Utils\SchemaReader\ISchemaReader;

	class SchemaReader {
		private null|ISchemaReader $reader = null;


		/**
		 * Instantiates a new schema reader object.
		 *
		 * @param PdoHelper $db
		 * @param Logger|null $log
		 */
		public function __construct(
			private readonly PdoHelper $db,
			private null|Logger $log = null
		) {
			if ($this->log === null) {
				$this->log = new Logger();
			}

			if (!$this->db->isActive()) {
				$this->log->error("Database connection is not active.");

				return;
			}

			$readers = [];
			$classes = get_declared_classes();

			foreach ($classes as $class) {
				if (is_subclass_of($class, ISchemaReader::class)) {
					$tmp = new $class($this->db, $this->log);

					if ($tmp->getDriver()->is(PdoDrivers::PDO_UNKNOWN)) {
						$this->log->warning("Schema reader '{$class}' does not have a valid driver set, skipping.");

						continue;
					}

					$readers[$tmp->getDriver()->getValue()] = $tmp;
				}
			}

			if (!isset($readers[$this->db->getDriver()->getValue()])) {
				$this->log->error("No schema reader found for driver '{$this->db->getDriver()->getValue()}'.");

				return;
			}

			$this->reader = $readers[$this->db->getDriver()->getValue()];

			return;
		}

		/**
		 * Fetches the columns for the specified database.
		 *
		 * @param string $dbName
		 * @return array
		 */
		public function fetchColumns(string $dbName) : array {
			if ($this->reader === null) {
				return [];
			}

			return $this->reader->fetchColumns($dbName);
		}

		/**
		 * Fetches all columns for any tables found in the schema.
		 *
		 * @param string $schemaName
		 * @return array
		 */
		public function fetchAllTableColumns(string $schemaName) : array {
			if ($this->reader === null) {
				return [];
			}

			return $this->reader->fetchAllTableColumns($schemaName);
		}
	}
