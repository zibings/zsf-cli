<?php

	namespace Zsf\Utils\Translators;

	class MySQLTranslator implements ITranslator {
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
