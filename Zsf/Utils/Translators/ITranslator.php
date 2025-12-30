<?php

	namespace Zsf\Utils\Translators;

	interface ITranslator {
		public function defaultPhpValue(string $type) : string;
		public function defaultTranslatedValue(string $type) : string;
		public function toPhpType(string $type) : string;
		public function toTranslatedType(string $type) : string;
	}
