<?php

	namespace Zsf\Utils\Translators;

	class SQLServerTranslator implements ITranslator {
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
