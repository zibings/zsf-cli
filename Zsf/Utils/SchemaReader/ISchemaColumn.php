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
		protected BaseDbTypes $modelType;
		protected string $phpType;


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
		 * @return BaseDbColumnFlags[]
		 */
		abstract public function getFlags() : array;

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
		abstract public function getModelType() : BaseDbTypes;

		/**
		 * Returns the PHP type for the column.
		 *
		 * @return string
		 */
		abstract public function getPhpType() : string;

		/**
		 * Internal method to parse the column data.
		 *
		 * @return void
		 */
		abstract protected function parseColumn() : void;
	}
