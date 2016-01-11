<?php

namespace libs;

/**
 * Description of Localizer
 *
 * @author Stepan
 */
class Localizer {

	const DAY_FORMAT_ABBR = 'abbr';
	const DAY_FORMAT_FULL = 'full';
	const DAY_FORMAT_ON_DAY = 'on_day';
	const USE_WIN_LOCALE = true;

	/** @var string[][] days of the week */
	static $dayStrings = [];

	public static function getDayName($n, $type) {
		if (!isset(self::$dayStrings[$type])) {
			self::$dayStrings[$type] = self::makeDays($type);
		}
		$days = self::$dayStrings[$type];
		if (array_key_exists($n, $days)) {
			return $days[$n];
		}
		return sprintf($days[0], $n);
	}

	private static function makeDays($type) {
		switch ($type) {
			case self::DAY_FORMAT_ABBR:
				return ['d%d', 'Po', 'Út', 'St', 'Čt', 'Pá', 'So', 'Ne'];
			case self::DAY_FORMAT_FULL:
				return ['Den %d', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota', 'Neděle'];
			case self::DAY_FORMAT_ON_DAY:
				return [ 'V den $d', 'V Pondělí', 'V Úterý', 'Ve Středu', 'Ve Čtvrtek',
					'V Pátek', 'V Sobotu', 'V Neděli'];
			default: return ['Den %d'];
		}
	}

	/**
	 * http://stackoverflow.com/questions/7765469/retrieving-day-names-in-php
	 * České znaky se ze strftime vygenerují ve špatném kódování

	  public static function makeDays() {

	  $locale = self::USE_WIN_LOCALE ? 'czech' : 'cs_CZ.UTF-8' ;

	  // let's remember the current local setting
	  $oldLocale = setlocale(LC_TIME, '0');
	  $days = array();

	  // 7 days in a week
	  for ($i = 0; $i < 7; $i++) {
	  setlocale(LC_TIME, $locale);
	  $days[$i + 1] = strftime('l', strtotime('this Monday +' . $i . ' days'));
	  setlocale(LC_TIME, $oldLocale);
	  }

	  return $days;
	  }
	 */
}
