<?php

	namespace Zsf\Utils\Translators;

	class PostgreSQLTranslator implements ITranslator {
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
