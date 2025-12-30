<?php

	namespace Zsf\Tests;

	use PHPUnit\Framework\TestCase;

	use function Zsf\Utils\makePluralString;
	use function Zsf\Utils\pluralizeClassName;

	class RandomFunctionsTest extends TestCase {
		public function test_makePluralString() : void {
			self::assertEquals('theses', makePluralString('thesis'));
			self::assertEquals('buses', makePluralString('bus'));
			self::assertEquals('boxes', makePluralString('box'));
			self::assertEquals('ladies', makePluralString('lady'));
			self::assertEquals('knives', makePluralString('knife'));
			self::assertEquals('analyses', makePluralString('analysis'));
			self::assertEquals('data', makePluralString('datum'));
			self::assertEquals('cats', makePluralString('cat'));

			return;
		}

		public function test_pluralizeClassName() : void {
			self::assertEquals('SwingVideos', pluralizeClassName('SwingVideo'));
			self::assertEquals('PersonRecords', pluralizeClassName('PersonRecord'));
			self::assertEquals('ChildItems', pluralizeClassName('ChildItem'));
			self::assertEquals('BusStops', pluralizeClassName('BusStop'));
			self::assertEquals('BoxFiles', pluralizeClassName('BoxFile'));
			self::assertEquals('LadyBugs', pluralizeClassName('LadyBug'));
			self::assertEquals('KnifeSets', pluralizeClassName('KnifeSet'));
			self::assertEquals('AnalysisReports', pluralizeClassName('AnalysisReport'));
			self::assertEquals('DatumPoints', pluralizeClassName('DatumPoint'));
			self::assertEquals('CatToyses', pluralizeClassName('CatToys'));

			return;
		}
	}
