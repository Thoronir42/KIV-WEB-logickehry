<?php

namespace model;

use model\database\views\ReservationExtended;

/**
 * Description of ReservationRenderer
 *
 * @author Stepan
 */
class ReservationRenderer {
	
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
		if($res->isEvent()){
			return 100;
		}
		
		$rFrom = strtotime($res->time_from);
		$rTo = strtotime($res->time_to);
		$rLength = $rTo - $rFrom;
		$h = date('H', $rLength);
		$m = date('i', $rLength);
		$rTime = 60 * $h + $m;
		// 19:00 - 7:00 = 13:00 ?????? only happened to eventReservation
		//echo date('H:i', $rFrom).' - '.date('H:i', $rTo)." =$h:$m = $rTime / $this->dayLength";
		return $rTime * 100 / $this->dayLength;
	}

	public function time($time) {
		$iTime = strtotime($time);
		return DatetimeManager::format($iTime, DatetimeManager::HUMAN_TIME_ONLY);
	}

	public function getDay($n, $type = \libs\Localizer::DAY_FORMAT_FULL){
		switch($type){
			default:
			return \libs\Localizer::getDayName($n, $type);
		}
	}

	public function getWeekStartDay() {
		return database\tables\Reservation::WEEK_START_DAY;
	}

	public function getWeekEndDay() {
		return database\tables\Reservation::WEEK_END_DAY;
	}

}
