<?php

	namespace Zsf\Utils\Translators;

	class SQLServerTranslator implements ITranslator {
		public function defaultPhpValue(string $type) : string {
			return match ($type) {
				'int', 'bigint', 'smallint', 'tinyint'                   => '0',
				'float', 'real', 'decimal', 'numeric'                    => '0.0',
				'date', 'datetime', 'datetime2', 'smalldatetime', 'time' => "new \\DateTime('now', new \\DateTimeZone('UTC'))",
				'bit'                                                    => 'false',
				default                                                  => 'null',
			};
		}

		public function defaultTranslatedValue(string $type) : string {
			return match ($type) {
				'int', 'bit'         => '0',
				'float'              => '0.0',
				'\DateTimeInterface' => 'GETDATE()',
				'null'               => 'NULL',
				default              => "''",
			};
		}

		public function toPhpType(string $type) : string {
			return match ($type) {
				'int', 'bigint', 'smallint', 'tinyint'                   => 'int',
				'float', 'real', 'decimal', 'numeric'                    => 'float',
				'bit'                                                    => 'bool',
				'date', 'datetime', 'datetime2', 'smalldatetime', 'time' => '\DateTimeInterface',
				default                                                  => 'string',
			};
		}

		public function toTranslatedType(string $type) : string {
			return match ($type) {
				'int'                => 'int',
				'float'              => 'decimal',
				'bool'               => 'bit',
				'\DateTimeInterface' => 'datetime',
				default              => 'string',
			};
		}
	}
