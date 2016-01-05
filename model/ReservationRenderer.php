<?php

namespace model;

use model\database\views\ReservationExtended;

/**
 * Description of ReservationRenderer
 *
 * @author Stepan
 */
class ReservationRenderer {

	private static $DAY_NAMES = [ 1 => 'V Pondělí', 'V Úterý', 'Ve Středu', 'Ve Čtvrtek',
		'V Pátek', 'V Sobotu', 'V Neděli'];
	public $dayStart,
			$dayEnd;

	/** @var int Length of day in minutes */
	private $dayLength;

	public function __construct($dayStart, $dayEnd) {
		$this->dayLength = (($this->dayEnd = $dayEnd) - ($this->dayStart = $dayStart)) * 60;
	}

	/**
	 * 
	 * @param ReservationExtended $res
	 */
	public function getStartPct($res) {
		$rStart = strtotime($res->time_from);
		$h = date('H', $rStart);
		$m = date('i', $rStart);
		$dayMin = 60 * ($h - $this->dayStart) + $m;
		return $dayMin * 100 / $this->dayLength;
	}

	/**
	 * 
	 * @param ReservationExtended $res
	 * @return double
	 */
	public function getWidthPct($res) {
		$rLength = strtotime($res->time_to) - strtotime($res->time_from);
		$h = date('H', $rLength);
		$m = date('i', $rLength);
		$rTime = 60 * $h + $m;
		return $rTime * 100 / $this->dayLength;
	}

	public function time($time) {
		$iTime = strtotime($time);
		return DatetimeManager::format($iTime, DatetimeManager::HUMAN_TIME_ONLY);
	}

	public function getDuringDayString($n) {
		if(isset(self::$DAY_NAMES[$n])){
			return self::$DAY_NAMES[$n];
		}
		return "V den $n";
	}

	public function getWeekStartDay() {
		return database\tables\Reservation::WEEK_START_DAY;
	}

	public function getWeekEndDay() {
		return database\tables\Reservation::WEEK_END_DAY;
	}

}
