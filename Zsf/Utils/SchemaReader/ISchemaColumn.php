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
		 * @param bool $preserveCase Optional flag to preserve casing on the returned name.
		 * @return string
		 */
		public function getName(bool $preserveCase = false) : string {
			if ($preserveCase) {
				return $this->data['name'] ?? '!!ERROR!!';
			}

			return $this->toCamelCase($this->data['name']) ?? '!!ERROR!!';
		}

		/**
		 * Returns the Stoic base model type for the column.
		 *
		 * @return BaseDbTypes
		 */
		abstract public function getModelType() : BaseDbTypes;

		/**
		 * Returns the default value for the PHP type of the column.
		 *
		 * @return string
		 */
		public function getPhpTypeDefaultValue() : string {
			$type = $this->getPhpType();

			return match ($type) {
				'int', 'float' => '0',
				'string'       => "''",
				'bool'         => 'false',
				'\DateTimeInterface' => 'new \DateTimeImmutable()',
				default       => 'null',
			};
		}

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

		/**
		 * Transforms a string to camelCase format.
		 *
		 * @param string $string
		 * @return string
		 */
		protected function toCamelCase(string $string) : string {
			$string = trim($string);

			if (empty($string)) {
				return '';
			}

			for ($i = 0; $i < strlen($string); $i++) {
				if (ctype_upper($string[$i]) && ($i > 0 && ctype_upper($string[$i - 1]))) {
					$string = substr_replace($string, strtolower($string[$i]), $i, 1);
				}
			}

			$string[0] = strtolower($string[0]);

			if (str_contains($string, '_') || str_contains($string, '-')) {
				$parts     = preg_split('/[_-]/', $string);
				$camelCase = array_shift($parts);

				foreach ($parts as $part) {
					$camelCase .= ucfirst(strtolower($part));
				}

				return $camelCase;
			}

			if (ctype_upper($string[0])) {
				return lcfirst($string);
			}

			if (ctype_upper(str_replace(['_', '-'], '', $string))) {
				$words     = preg_split('/[_-]/', strtolower($string));
				$camelCase = array_shift($words);

				foreach ($words as $word) {
					$camelCase .= ucfirst($word);
				}

				return $camelCase;
			}

			return $string;
		}
	}
