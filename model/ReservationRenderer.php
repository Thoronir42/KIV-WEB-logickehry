<?php

namespace model;

use model\database\IRenderableWeekEntity,
	model\database\tables\Reservation;
use libs\DatetimeManager;

use libs\Localizer;

/**
 * Description of ReservationRenderer
 *
 * @author Stepan
 */
class ReservationRenderer {

	public static function getInstance() {
		return new ReservationRenderer(Reservation::EARLY_RESERVATION, Reservation::LATE_RESERVATION);
	}

	public $dayStart,
			$dayEnd;

	/** @var int Length of day in minutes */
	private $dayLength;

	public function __construct($dayStart, $dayEnd) {
		$this->dayEnd = $dayEnd;
		$this->dayStart  = $dayStart;
		$this->dayLength = (($this->dayEnd) - ($this->dayStart)) * 60 * 60;
	}

	/**
	 * 
	 * @param IRenderableWeekEntity $res
	 */
	public function getStartPct($res) {
		$rStart = strtotime($res->getTimeFrom());
		$h = date('H', $rStart);
		$m = date('i', $rStart);
		$dayMin = 60 * (60 * ($h - $this->dayStart) + $m);
		return $dayMin * 100 / $this->dayLength;
	}

	/**
	 * 
	 * @param IRenderableWeekEntity $res
	 * @return double
	 */
	public function getWidthPct($res) {
		$rLength = $res->getTimeLength();
		/*
		$mins = $rLength / 60;
		$h = floor($mins / 60);
		$m = $mins % 60;
		
		$rTime = 60 * ($h * 60 + $m);
		 */
		return max(3, $rLength * 100 / $this->dayLength);
	}

	public function time($time) {
		$iTime = strtotime($time);
		return DatetimeManager::format($iTime, DatetimeManager::HUMAN_TIME_ONLY);
	}

	public function getDay($n, $type = Localizer::DAY_FORMAT_FULL) {
		return Localizer::getDayName($n, $type);
	}

	public function getWeekStartDay() {
		return Reservation::WEEK_START_DAY;
	}

	public function getWeekEndDay() {
		return Reservation::WEEK_END_DAY;
	}
	
	public function getDayStart() {
		return sprintf("%02d", $this->dayStart);
	}

	public function getDayEnd() {
		return sprintf("%02d", $this->dayEnd);
	}
	
	public function getEnabledHours(){
		$return = [];
		
		for($i = $this->dayStart; $i < $this->dayEnd; $i++){
			$return[] = $i;
		}
		
		return implode(',', $return);
	}

}
