<?php

namespace model;

use model\database\IRenderableWeekEntity,
	model\database\tables\Reservation;

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
		$this->dayLength = (($this->dayEnd = $dayEnd) - ($this->dayStart = $dayStart)) * 60;
	}

	/**
	 * 
	 * @param IRenderableWeekEntity $res
	 */
	public function getStartPct($res) {
		$rStart = strtotime($res->getTimeFrom());
		$h = date('H', $rStart);
		$m = date('i', $rStart);
		$dayMin = 60 * ($h - $this->dayStart) + $m;
		return $dayMin * 100 / $this->dayLength;
	}

	/**
	 * 
	 * @param IRenderableWeekEntity $re
	 */
	public function renderEntity($re) {
		$return = sprintf("<div class=\"%s%s\" style=\"left:%.2f%%; width:%.2f%%;\">", $re->getType(), $re->hasGameAssigned() ? ' game' . $re->getGameTypeID() : "", $this->getStartPct($re), $this->getWidthPct($re)
		);
		$return .= sprintf("%s<br><small>%s</small>", $re->getTitle(), $re->getSubtitle());
		$return .= "</div>";
		return $return;
	}

	/**
	 * 
	 * @param IRenderableWeekEntity $res
	 * @return double
	 */
	public function getWidthPct($res) {
		$rLength = $res->getTimeLength();
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

	public function getDay($n, $type = \libs\Localizer::DAY_FORMAT_FULL) {
		return \libs\Localizer::getDayName($n, $type);
	}

	public function getWeekStartDay() {
		return database\tables\Reservation::WEEK_START_DAY;
	}

	public function getWeekEndDay() {
		return database\tables\Reservation::WEEK_END_DAY;
	}

}
