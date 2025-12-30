<?php

	namespace Zsf\Utils\Translators;

	class TypeScriptTranslator implements ITranslator {
		public function toPhpType(string $type) : string {
			return match ($type) {
				'number'  => 'float|int',
				'string'  => 'string',
				'boolean' => 'bool',
				'Array'   => 'array',
				'object'  => 'object',
				'null'    => 'null',
				'default' => 'mixed',
			};
		}

		public function toTranslatedType(string $type) : string {
			return match ($type) {
				'float', 'int' => 'number',
				'string'       => 'string',
				'bool'         => 'boolean',
				'array'        => 'Array',
				'object'       => 'object',
				'null'         => 'null',
				'default'      => 'any',
			};
		}
	}
