<?php

	namespace Zsf\Utils\Translators;

	interface ITranslator {
		public function toPhpType(string $type) : string;
		public function toTranslatedType(string $type) : string;
	}
