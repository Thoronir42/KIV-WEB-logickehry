<?php

namespace libs;

/**
 * Description of Localizer
 *
 * @author Stepan
 */
class Localizer {

	const USE_WIN_LOCALE = true;

	/** @var string[] days of the week */
	static $dayAbbrs;

	public static function getDayName($n) {
		if (!isset(self::$dayAbbrs) || is_null(self::$dayAbbrs)) {
			self::$dayAbbrs = self::makeDays();
		}
		if (array_key_exists($n, self::$dayAbbrs)) {
			return self::$dayAbbrs[$n];
		}
		return 'Den' . $n;
	}

	private static function makeDays() {
		return [1 => 'Po', 'Út', 'St', 'Čt', 'Pá', 'So', 'Ne'];
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
