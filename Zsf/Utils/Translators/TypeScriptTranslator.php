<?php

	namespace Zsf\Utils\Translators;

	class TypeScriptTranslator implements ITranslator {
		public function defaultPhpValue(string $type) : string {
			return match ($type) {
				'number'  => '0',
				'string'  => "''",
				'boolean' => 'false',
				'Array'   => '[]',
				'object'  => '(object)[]',
				'Date'    => "new \\DateTime('now', new \\DateTimeZone('UTC'))",
				default   => 'null',
			};
		}

		public function defaultTranslatedValue(string $type) : string {
			return match ($type) {
				'float'              => '0.0',
				'int'                => '0',
				'string'             => "''",
				'bool'               => 'false',
				'array'              => '[]',
				'object'             => '{}',
				'\DateTimeInterface' => 'new Date()',
				default              => 'null',
			};
		}

		public function toPhpType(string $type) : string {
			return match ($type) {
				'number'  => 'float|int',
				'string'  => 'string',
				'boolean' => 'bool',
				'Array'   => 'array',
				'object'  => 'object',
				'null'    => 'null',
				'Date'    => '\DateTimeInterface',
				default   => 'mixed',
			};
		}

		public function toTranslatedType(string $type) : string {
			return match ($type) {
				'float', 'int'       => 'number',
				'string'             => 'string',
				'bool'               => 'boolean',
				'array'              => 'Array',
				'object'             => 'object',
				'null'               => 'null',
				'\DateTimeInterface' => 'Date',
				default              => 'any',
			};
		}
	}
