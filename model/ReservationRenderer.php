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
		echo ($rStart = strtotime($res->time_from))."<br>";
		$h = date('H', $rStart); $m = date('i', $rStart);
		echo"$h:$m<br>";
		echo ($dayMin = 60*($h - $this->dayStart)+$m)."/$this->dayLength = ";
		echo $dayMin / $this->dayLength;
	}

}
