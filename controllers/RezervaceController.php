<?php

namespace controllers;

use model\DatetimeManager,
	model\GameTypeManager,
	model\ReservationManager;

class RezervaceController extends Controller {

	public static function getDefaultAction() {
		return "vypis";
	}

	public function startUp() {
		parent::startUp();
	}

	protected function buildSubmenu() {
		return false;
		/* $menu = [
		  ["urlParams" => ["controller" => "rezervace", "action"=>"vypis"],
		  "label" => "Vypis"
		  ],
		  ];
		  return $menu; */
	}

	public function renderVypis() {
		$week = $this->getParam("tyden");
		if (!is_numeric($week)) {
			$week = 0;
		}
		$timePars = DatetimeManager::getWeeksBounds($week, DatetimeManager::DB_FORMAT);
		$reservations = ReservationManager::fetchWithinTimespan(
						$this->pdoWrapper, DatetimeManager::format($timePars, DatetimeManager::DB_FORMAT));
		$reservationDays = [];
		foreach ($reservations as $r) {
			$day = date("w", strtotime($reservations[0]->time_from));
			if (!isset($reservationDays[$day])) {
				$reservationDays[$day] = [];
			}
			$reservationDays[$day][] = $r;
		}

		$this->template["pageTitle"] = $this->makeVypisTitle($week);
		$this->template["timePars"] = DatetimeManager::format($timePars, DatetimeManager::HUMAN_DATE_ONLY_FORMAT);
		$this->template['games'] = GameTypeManager::fetchAll($this->pdoWrapper);
		$this->template['desks'] = $this->pdoWrapper->getDesks();
		$this->template["reservationDays"] = $reservationDays;
		$this->template['weekShift'] = $this->makeWeekLinks($week);
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

	private function makeWeekLinks($week) {
		$ret = [];
		$ret['prev'] = [ 'url' => ['controller' => 'rezervace', 'action' => 'vypis', 'tyden' => ($week - 1)],
			'glyph' => 'glyphicon glyphicon-chevron-left'];
		$ret['curr'] = [ 'url' => ['controller' => 'rezervace', 'action' => 'vypis'],
			'glyph' => 'glyphicon glyphicon-record'];
		$ret['next'] = [ 'url' => ['controller' => 'rezervace', 'action' => 'vypis', 'tyden' => ($week + 1)],
			'glyph' => 'glyphicon glyphicon-chevron-right'];
		return $ret;
	}

	public function doRezervovat() {
		$reservation = \model\database\tables\Reservation::fromPOST();
		if (!$reservation->readyForInsert()) {
			
		}
		$pars = $reservation->asArray();
		$pars['reservee_user_id'] = $this->user->user_id;
	}

}
