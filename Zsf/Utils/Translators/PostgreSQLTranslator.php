<?php

	namespace Zsf\Utils\Translators;

	class PostgreSQLTranslator implements ITranslator {
		public function defaultPhpValue(string $type) : string {
			return match ($type) {
				'int', 'bigint', 'smallint'        => '0',
				'float', 'double', 'numeric'       => '0.0',
				'date', 'timestamp', 'timestamptz' => "new \\DateTime('now', new \\DateTimeZone('UTC'))",
				'bool', 'boolean'                  => 'false',
				default                            => 'null',
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
				'integer', 'bigint', 'smallint'    => 'int',
				'real', 'double', 'numeric'        => 'float',
				'boolean'                          => 'bool',
				'date', 'timestamp', 'timestamptz' => '\DateTimeInterface',
				default                            => 'string',
			};
		}

		public function toTranslatedType(string $type) : string {
			return match ($type) {
				'int'                => 'integer',
				'float'              => 'real',
				'bool'               => 'boolean',
				'\DateTimeInterface' => 'timestamp',
				default              => 'text',
			};
		}
	}
