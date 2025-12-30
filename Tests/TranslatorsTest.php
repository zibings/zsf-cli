<?php

	namespace Zsf\Tests;

	use PHPUnit\Framework\TestCase;

	use Zsf\Utils\Translators;

	class TranslatorsTest extends TestCase {
		public function test_JavaScriptTranslator() : void {
			$js = new Translators\JavaScriptTranslator();

			self::assertEquals('float|int', $js->toPhpType('number'));
			self::assertEquals('string', $js->toPhpType('string'));
			self::assertEquals('bool', $js->toPhpType('boolean'));
			self::assertEquals('array', $js->toPhpType('Array'));
			self::assertEquals('object', $js->toPhpType('object'));
			self::assertEquals('null', $js->toPhpType('null'));
			self::assertEquals('mixed', $js->toPhpType('default'));

			self::assertEquals('number', $js->toTranslatedType('float'));
			self::assertEquals('number', $js->toTranslatedType('int'));
			self::assertEquals('string', $js->toTranslatedType('string'));
			self::assertEquals('boolean', $js->toTranslatedType('bool'));
			self::assertEquals('Array', $js->toTranslatedType('array'));
			self::assertEquals('object', $js->toTranslatedType('object'));
			self::assertEquals('null', $js->toTranslatedType('null'));
			self::assertEquals('any', $js->toTranslatedType('mixed'));

			return;
		}

		public function test_MySQLTranslator() : void {
			$mysql = new Translators\MySQLTranslator();

			self::assertEquals('int', $mysql->toPhpType('int'));
			self::assertEquals('int', $mysql->toPhpType('tinyint'));
			self::assertEquals('int', $mysql->toPhpType('bigint'));
			self::assertEquals('float', $mysql->toPhpType('float'));
			self::assertEquals('float', $mysql->toPhpType('double'));
			self::assertEquals('float', $mysql->toPhpType('decimal'));
			self::assertEquals('\DateTimeInterface', $mysql->toPhpType('date'));
			self::assertEquals('\DateTimeInterface', $mysql->toPhpType('datetime'));
			self::assertEquals('\DateTimeInterface', $mysql->toPhpType('timestamp'));
			self::assertEquals('bool', $mysql->toPhpType('bool'));
			self::assertEquals('bool', $mysql->toPhpType('boolean'));
			self::assertEquals('string', $mysql->toPhpType('default'));

			self::assertEquals('int', $mysql->toTranslatedType('int'));
			self::assertEquals('decimal', $mysql->toTranslatedType('float'));
			self::assertEquals('datetime', $mysql->toTranslatedType('date'));
			self::assertEquals('boolean', $mysql->toTranslatedType('bool'));
			self::assertEquals('string', $mysql->toTranslatedType('default'));

			return;
		}

		public function test_PostgreSQLTranslator() : void {
			$pgsql = new Translators\PostgreSQLTranslator();

			self::assertEquals('int', $pgsql->toPhpType('integer'));
			self::assertEquals('float', $pgsql->toPhpType('real'));
			self::assertEquals('\DateTimeInterface', $pgsql->toPhpType('timestamp'));
			self::assertEquals('bool', $pgsql->toPhpType('boolean'));
			self::assertEquals('string', $pgsql->toPhpType('default'));

			self::assertEquals('integer', $pgsql->toTranslatedType('int'));
			self::assertEquals('real', $pgsql->toTranslatedType('float'));
			self::assertEquals('timestamp', $pgsql->toTranslatedType('date'));
			self::assertEquals('boolean', $pgsql->toTranslatedType('bool'));
			self::assertEquals('string', $pgsql->toTranslatedType('default'));

			return;
		}

		public function test_SQLServerTranslator() : void {
			$sqlsrv = new Translators\SQLServerTranslator();

			self::assertEquals('int', $sqlsrv->toPhpType('int'));
			self::assertEquals('float', $sqlsrv->toPhpType('float'));
			self::assertEquals('\DateTimeInterface', $sqlsrv->toPhpType('datetime'));
			self::assertEquals('bool', $sqlsrv->toPhpType('bit'));
			self::assertEquals('string', $sqlsrv->toPhpType('default'));

			self::assertEquals('int', $sqlsrv->toTranslatedType('int'));
			self::assertEquals('float', $sqlsrv->toTranslatedType('float'));
			self::assertEquals('datetime', $sqlsrv->toTranslatedType('date'));
			self::assertEquals('bit', $sqlsrv->toTranslatedType('bool'));
			self::assertEquals('string', $sqlsrv->toTranslatedType('default'));

			return;
		}

		public function test_TypeScriptTranslator() : void {
			$ts = new Translators\TypeScriptTranslator();

			self::assertEquals('number', $ts->toPhpType('number'));
			self::assertEquals('string', $ts->toPhpType('string'));
			self::assertEquals('bool', $ts->toPhpType('boolean'));
			self::assertEquals('array', $ts->toPhpType('Array'));
			self::assertEquals('object', $ts->toPhpType('object'));
			self::assertEquals('null', $ts->toPhpType('null'));
			self::assertEquals('mixed', $ts->toPhpType('default'));

			self::assertEquals('number', $ts->toTranslatedType('float'));
			self::assertEquals('number', $ts->toTranslatedType('int'));
			self::assertEquals('string', $ts->toTranslatedType('string'));
			self::assertEquals('boolean', $ts->toTranslatedType('bool'));
			self::assertEquals('Array', $ts->toTranslatedType('array'));
			self::assertEquals('object', $ts->toTranslatedType('object'));
			self::assertEquals('null', $ts->toTranslatedType('null'));
			self::assertEquals('any', $ts->toTranslatedType('mixed'));

			return;
		}
	}
