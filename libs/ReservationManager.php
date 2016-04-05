<?php

namespace libs;

use model\services\Reservations;
use model\services\Events;
use model\database\IRenderableWeekEntity;
use model\database\tables\Event;
use model\database\views\ReservationExtended;

/**
 * Description of ReservationManager
 *
 * @author Stepan
 */
class ReservationManager {

	/** @var Reservations */
	private $reservations;

	/** @var Events */
	private $events;

	public function __construct($pdo) {
		$this->reservations = new Reservations($pdo);
		$this->events = new Events($pdo);
	}

	public function prepareReservationWeek($week = 0, $user_id = null) {
		$weekBounds = DatetimeManager::getWeeksBounds($week);
		$dbTimePars = DatetimeManager::format($weekBounds, DatetimeManager::DB_FULL);

		$reservations = $this->reservations->fetchWithin($dbTimePars['time_from'], $dbTimePars['time_to'], $user_id);
		$events = $this->events->fetchWithinTimespan($dbTimePars['time_from'], $dbTimePars['time_to']);

		$weekEntityGroups = ['events' => $events, 'reservations' => $reservations];

		$return = [];

		$return['days'] = self::prepareReservationDays($weekBounds['time_from'], $weekEntityGroups);
		$return["pageTitle"] = self::makeVypisTitle($week);
		$return['timeSpan'] = DatetimeManager::format($weekBounds, DatetimeManager::HUMAN_DATE_ONLY);
		$return['currentWeekGames'] = $this->countGamesOnReservations($reservations);
		return $return;
	}

	private function createWeekContainer($timeFrom) {
		$reservationDays = [];

		for ($i = 0; $i < 7; $i++) {
			$day = strtotime(("+ $i days"), $timeFrom);
			$reservationDays[$i + 1] = [
				'date' => date('d.m.', $day),
				'weekEntities' => [],
				'year' => date('Y', $day),
			];
		}
		return $reservationDays;
	}

	/**
	 * 
	 * @param type $timeFrom
	 * @param IRenderableWeekEntity[][] $weekEntityGroups
	 * @return boolean
	 */
	private function prepareReservationDays($timeFrom, $weekEntityGroups) {
		$reservationDays = $this->createWeekContainer($timeFrom);
		foreach ($weekEntityGroups as $group) {
			foreach ($group as $entity) {
				$day = date("w", strtotime($entity->getDate()));
				if ($day == 0) {
					$day = 7;
				}
				$reservationDays[$day]['weekEntities'][] = $entity;
			}
		}
		for ($i = 7; $i >= 1; $i--) {
			if (!empty($reservationDays[$i]['weekEntities'])) {
				$reservationDays[$i]['last'] = true;
				break;
			}
		}
		return $reservationDays;
	}

	/**
	 * 
	 * @param ReservationExtended $reservations
	 */
	private function countGamesOnReservations($reservations) {
		$games = [];
		foreach ($reservations as $reservation) {
			$gameTypeId = $reservation->getGameTypeID();
			$games[$gameTypeId] = ( isset($games[$gameTypeId]) ) ? $games[$gameTypeId] + 1 : 1;
		}

		return $games;
	}

	private function makeVypisTitle($week) {
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
