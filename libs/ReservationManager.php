<?php

namespace libs;

use model\database\views\ReservationExtended;
use model\database\tables\Event;

/**
 * Description of ReservationManager
 *
 * @author Stepan
 */
class ReservationManager {

	public static function prepareReservationWeek($pdo, $week = 0, $user_id = null) {
		$timePars = DatetimeManager::getWeeksBounds($week);
		$dbTimePars = DatetimeManager::format($timePars, DatetimeManager::DB_FULL);

		$return = [];

		$return['reservationDays'] = self::prepareReservationDays($pdo, $timePars['time_from'], $dbTimePars, $user_id);
		$return["pageTitle"] = self::makeVypisTitle($week);
		$return['timePars'] = $timePars;

		return $return;
	}

	private static function prepareReservationDays($pdo, $timeFrom, $dbTimePars, $user_id = null) {
		$reservations = ReservationExtended::fetchWithinTimespan($pdo, $dbTimePars, $user_id);
		$events = Event::fetchWithinTimespan($pdo, $dbTimePars);
		
		$reservationDays = [];

		for ($i = 0; $i < 7; $i++) {
			$day = strtotime(("+ $i days"), $timeFrom);
			$reservationDays[$i + 1] = [
				'date' => date('d.m.', $day),
				'weekEntities' => [],
				'year' => date('Y', $day),
			];
		}
		
		foreach ($reservations as $r) {
			$day = date("w", strtotime($r->getDate()));
			$reservationDays[$day]['weekEntities'][] = $r;
		}
		foreach ($events as $e) {
			$day = date("w", strtotime($e->getDate()));
			$reservationDays[$day]['weekEntities'][] = $e;
		}
		for($i = 6; $i >= 0; $i--){
			if(!empty($reservationDays[$i]['weekEntities'])){
				$reservationDays[$i]['last'] = true;
				break;
			}
		}
		return $reservationDays;
	}

	private static function makeVypisTitle($week) {
		switch ($week) {
			case -1: return "Výpis rezervací předcházejícího týdne";
			case 0: return "Výpis rezervací aktuálního týdne";
			case 1: return "Výpis rezervací následujícího týdne";
		}
		if ($week < 0) {
			$w = -1 * $week;
			$filler = "před";
		} else {
			$w = $week;
			$filler = "za";
		}
		return "Výpis rezervací $filler $w týdny";
	}

}
