<?php

namespace controllers;

use \model\database\views as Views,
	\model\database\tables as Tables;
use model\DatetimeManager;

class LetisteController extends Controller {

	public static function getDefaultAction() {
		return 'rezervace';
	}

	public function __construct($support) {
		parent::__construct($support);
		$this->layout = "letiste.twig";
	}

	public function renderRezervace() {
		$this->addCss('input-specific.css');
		$this->addJs('input-specific.js');
		$this->addCss('rezervace_vypis.css');
		$week = $this->getParam("tyden");
		if (!is_numeric($week)) {
			$week = 0;
		}

		$timePars = DatetimeManager::getWeeksBounds($week);
		$dbTimePars = DatetimeManager::format($timePars, DatetimeManager::DB_FULL);


		$game_type_id = $this->getParam("game_id");
		if (Views\GameTypeExtended::fetchById($this->pdo, $game_type_id)) {
			$this->template['defaultGame'] = $game_type_id;
		}

		$this->template["reservationDays"] = $this->prepareReservationDays($timePars['time_from'], $dbTimePars);
		$this->template["reservationTypes"] = Tables\Reservation::getTypes($this->user->isSupervisor());
		$this->template['resRend'] = new \model\ReservationRenderer(Tables\Reservation::EARLY_RESERVATION, Tables\Reservation::LATE_RESERVATION);
		$this->template['games'] = Views\GameTypeExtended::fetchAll($this->pdo);
		$this->template["pageTitle"] = $this->makeVypisTitle($week);
		$this->template["timeSpan"] = DatetimeManager::format($timePars, DatetimeManager::HUMAN_DATE_ONLY);
	}

	private function prepareReservationDays($timeFrom, $dbTimePars) {
		$reservations = Views\ReservationExtended::fetchWithinTimespan(
						$this->pdo, $dbTimePars);
		$reservationDays = [0 => ['date' => null]];

		for ($i = 0; $i < 7; $i++) {
			$reservationDays[$i + 1] = [
				'date' => date('d.m.', strtotime(("+ $i days"), $timeFrom)),
				'reservations' => [],
			];
		}

		foreach ($reservations as $r) {
			$day = date("w", strtotime($reservations[0]->time_from));
			$reservationDays[$day]['reservations'][] = $r;
		}
		return $reservationDays;
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

	public function preRender() {
		
	}

}
