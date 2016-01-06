<?php

namespace model;

/**
 * Description of DatetimeManager
 *
 * @author Stepan
 */
class DatetimeManager {

	const DB_FULL = "y-m-d H:i:s";
	const DB_DATE_ONLY = 'y-m-d';
	const DB_TIME_ONLY = 'H:i:s';
	const HUMAN_FULL = "d/m/y H:i:s";
	const HUMAN_DATE_ONLY = "d.m.Y";
	const HUMAN_TIME_ONLY = 'H:i';

	public static function getWeeksBounds($weekOffset = 0, $format = null) {
		if ($weekOffset == 0) {
			$looseWeek = time();
		} else {
			if ($weekOffset < 0) {
				$looseWeek = strtotime("$weekOffset week");
			} else {
				$looseWeek = strtotime("+$weekOffset week");
			}
		}
		$isMonday = date('w', $looseWeek) == 1;

		$start = strtotime((($isMonday) ? 'this' : 'last') . ' Monday', $looseWeek);

		$end = strtotime(($isMonday ? 'next' : 'this') . ' Monday', $looseWeek);

		$return = ['time_from' => $start, 'time_to' => $end];
		if ($format == null) {
			return $return;
		}
		return self::format($return, $format);
	}

	public static function format($timePars, $format) {
		$return = [];
		if (!is_array($timePars)) {
			return date($format, $timePars);
		}
		foreach ($timePars as $key => $val) {
			$return[$key] = date($format, $val);
		}
		return $return;
	}

}
