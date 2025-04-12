<?php

	namespace Zsf\Utils\SchemaReader;

	use Stoic\Pdo\BaseDbColumnFlags;
	use Stoic\Pdo\BaseDbTypes;

	/**
	 * Abstract class defining the interface schema columns must implement.
	 *
	 * @package Zsf\Utils\SchemaReader
	 */
	abstract class ISchemaColumn {
		protected array $flags = [];
		protected BaseDbTypes $type;


		/**
		 * Instantiates a new ISchemaColumn object with the provided data.
		 *
		 * @param array $data
		 */
		public function __construct(
			protected array $data = []
		) {
			$this->parseColumn();

			return;
		}

		/**
		 * Returns all appropriate flags for the column.
		 *
		 * @return array
		 */
		abstract public function getFlags() : array;

		/**
		 * Returns the Stoic base db type for the column.
		 *
		 * @return BaseDbTypes
		 */
		abstract public function getType() : BaseDbTypes;

		/**
		 * Internal method to parse the column data.
		 *
		 * @return void
		 */
		abstract protected function parseColumn() : void;
	}
