<?php

	namespace Zsf\Utils\Translators;

	class MySQLTranslator implements ITranslator {
		public function defaultPhpValue(string $type) : string {
			return match ($type) {
				'int', 'tinyint', 'bigint'      => '0',
				'float', 'double', 'decimal'    => '0.0',
				'date', 'datetime', 'timestamp' => "new \\DateTime('now', new \\DateTimeZone('UTC'))",
				'bool', 'boolean'               => 'false',
				default                         => 'null'
			};
		}

		public function defaultTranslatedValue(string $type) : string {
			return match ($type) {
				'int', 'bool'        => '0',
				'float'              => '0.0',
				'\DateTimeInterface' => 'CURRENT_TIMESTAMP',
				'null'               => 'NULL',
				default              => "''",
			};
		}

		public function toPhpType(string $type) : string {
			return match ($type) {
				'int', 'tinyint', 'bigint'      => 'int',
				'float', 'double', 'decimal'    => 'float',
				'date', 'datetime', 'timestamp' => '\DateTimeInterface',
				'bool', 'boolean'               => 'bool',
				default                         => 'string',
			};
		}

		public function toTranslatedType(string $type) : string {
			return match ($type) {
				'int'   => 'int',
				'float' => 'decimal',
				'date'  => 'datetime',
				'bool'  => 'boolean',
				default => 'string',
			};
		}
	}
