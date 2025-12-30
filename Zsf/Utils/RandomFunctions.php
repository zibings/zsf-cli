<?php

	namespace Zsf\Utils;

	/**
	 * Makes a string plural based on common English language rules.
	 *
	 * @param string $string
	 * @return string
	 */
	function makePluralString(string $string) : string {
		$lower     = strtolower($string);
		$irregular = [
			'person'     => 'people',
			'man'        => 'men',
			'woman'      => 'women',
			'child'      => 'children',
			'tooth'      => 'teeth',
			'foot'       => 'feet',
			'mouse'      => 'mice',
			'goose'      => 'geese',
			'ox'         => 'oxen',
			'leaf'       => 'leaves',
			'life'       => 'lives',
			'knife'      => 'knives',
			'wife'       => 'wives',
			'half'       => 'halves',
			'elf'        => 'elves',
			'loaf'       => 'loaves',
			'potato'     => 'potatoes',
			'tomato'     => 'tomatoes',
			'cactus'     => 'cacti',
			'focus'      => 'foci',
			'fungus'     => 'fungi',
			'nucleus'    => 'nuclei',
			'syllabus'   => 'syllabi',
			'analysis'   => 'analyses',
			'diagnosis'  => 'diagnoses',
			'thesis'     => 'theses',
			'phenomenon' => 'phenomena',
			'criterion'  => 'criteria',
			'datum'      => 'data',
			'bus'        => 'buses',
		];

		if (isset($irregular[$lower])) {
			return $irregular[$lower];
		}

		$lastChar = substr($string, -1);
		$lastTwo  = substr($string, -2);
		$vowels   = ['a', 'e', 'i', 'o', 'u'];

		if ($lastChar == 'y' && !in_array(substr($lower, -2, 1), $vowels)) {
			return substr($string, 0, -1) . 'ies';
		}

		if (in_array($lastChar, ['s', 'x', 'z']) || in_array($lastTwo, ['ss', 'sh', 'ch'])) {
			return $string . 'es';
		}

		if ($lastChar == 'f') {
			return substr($string, 0, -1) . 'ves';
		}

		if ($lastTwo == 'fe') {
			return substr($string, 0, -2) . 'ves';
		}

		if ($lastChar == 'o' && !in_array(substr($lower, -2, 1), $vowels)) {
			return $string . 'es';
		}

		return $string . 's';
	}

	/**
	 * Pluralizes a class name by converting the last CamelCase or snake_case segment to its plural form.
	 *
	 * @param string $className
	 * @return string
	 */
	function pluralizeClassName(string $className) : string {
		preg_match_all('/[A-Z][a-z0-9]*|[a-z0-9]+/', $className, $matches);
		$parts = $matches[0];

		if (empty($parts)) {
			return $className;
		}

		$last = array_pop($parts);
		$last = makePluralString($last);
		$parts[] = $last;

		if (stripos($className, '_') !== false) {
			return implode('_', $parts);
		}

		return implode('', $parts);
	}
