<?php

	namespace Zsf\Utils\SchemaReader;

	use Stoic\Log\Logger;
	use Stoic\Pdo\PdoDrivers;
	use Stoic\Pdo\PdoHelper;

	/**
	 * Abstract class defining the interface schema readers must implement.
	 *
	 * @package Zsf\Utils\SchemaReader
	 */
	abstract class ISchemaReader {
		protected PdoDrivers $driver;


		/**
		 * Instantiates a new ISchemaReader object with database and logging resources.
		 *
		 * @param PdoHelper $db
		 * @param Logger|null $log
		 */
		public function __construct(
			protected readonly PdoHelper $db,
			protected null|Logger $log = null
		) {
			if ($this->log === null) {
				$this->log = new Logger();
			}

			if (!$this->db->isActive()) {
				$this->log->error("Database connection is not active.");
			}

			$this->driver = new PdoDrivers(PdoDrivers::PDO_UNKNOWN);

			return;
		}

		/**
		 * Fetches all columns for any tables found in the schema.
		 *
		 * @param string $schemaName
		 * @return array
		 */
		abstract public function fetchAllTableColumns(string $schemaName) : array;

		/**
		 * Fetches the columns for the specified table.
		 *
		 * @param string $tableName
		 * @return array
		 */
		abstract public function fetchTableColumns(string $tableName) : array;

		/**
		 * Returns the driver used by the schema reader.
		 *
		 * @return PdoDrivers
		 */
		public function getDriver() : PdoDrivers {
			return $this->driver;
		}

		/**
		 * Internal method to set schema reader's driver.
		 *
		 * @param int|PdoDrivers $driver
		 * @throws \ReflectionException
		 * @return void
		 */
		protected function setDriver(int|PdoDrivers $driver) : void {
			$this->driver = PdoDrivers::tryGet($driver);

			return;
		}
	}
