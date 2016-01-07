<?php

namespace controllers;

use model\DatetimeManager;
use \model\database\tables as Tables,
	\model\database\views as Views;

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
		$this->addCss('input-specific.css');
		$this->addJs('input-specific.js');
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

		$this->template['formAction'] = ['controller' => 'rezervace', 'action' => 'rezervovat'];
		
		$this->template["pageTitle"] = $this->makeVypisTitle($week);
		$this->template["timeSpan"] = DatetimeManager::format($timePars, DatetimeManager::HUMAN_DATE_ONLY);
		$this->template['games'] = $this->prepareGames($dbTimePars);
		$this->template['desks'] = Tables\Desk::fetchAll($this->pdo);
		$this->template['weekShift'] = $this->makeWeekLinks($week);
	}

	private function prepareReservationDays($timeFrom, $dbTimePars) {
		$reservations = Views\ReservationExtended::fetchWithinTimespan(
						$this->pdo, $dbTimePars);
		$reservationDays = [0 => ['date' => null]];

		for ($i = 0; $i < 7; $i++) {
			$day = strtotime(("+ $i days"), $timeFrom);
			$reservationDays[$i + 1] = [
				'date' => date('d.m.', $day),
				'reservations' => [],
				'year' => date('Y', $day),
			];
		}

		foreach ($reservations as $r) {
			$day = date("w", strtotime($reservations[0]->reservation_date));
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

	private function makeWeekLinks($week) {
		$ret = [];
		$ret['next'] = $ret['curr'] = $ret['prev'] = [ 'url' => ['controller' => 'rezervace', 'action' => 'vypis']];
		$ret['prev']['glyph'] = 'glyphicon glyphicon-chevron-left';
		$ret['curr']['glyph'] = 'glyphicon glyphicon-record';
		$ret['next']['glyph'] = 'glyphicon glyphicon-chevron-right';
		if ($week - 1 != 0) {
			$ret['prev']['url']['tyden'] = $week - 1;
		}
		if ($week + 1 != 0) {
			$ret['next']['url']['tyden'] = $week + 1;
		}
		return $ret;
	}

	private function prepareGames($timePars) {
		$games = Views\GameTypeExtended::fetchAll($this->pdo);
		$resCounts = Views\ReservationExtended::countByGametypeWithinTimespan($this->pdo, $timePars);
		foreach ($resCounts as $count) {
			$games[$count['game_type_id']]->reservationCount = $count['count'];
		}
		return $games;
	}

	public function doRezervovat() {
		var_dump($_POST);
		$game_type_id = $this->getParam('game_type_id', INPUT_POST);
		$reservation = \model\database\tables\Reservation::fromPOST();
		$reservation->reservee_user_id = $this->user->user_id;
		

		if (!$reservation->readyForInsert()) {
			$this->message('Vstupní pole rezervace nebyla správně vyplněna - rezervae nebyla přidána');
			$this->redirectPars('rezervace', 'vypis');
		}

		$v = $this->validateReservation($reservation, $game_type_id);
		if (!$v['result']){
			$this->message($v['message'], \libs\MessageBuffer::LVL_WAR);
			$this->redirectPars('rezervace', 'vypis');
		}
		
		$pars = $reservation->asArray();
		Tables\Reservation::insert($this->pdo, $pars);
	}

	/**
	 * 
	 * @param Tables\Reservation $reservation
	 * @return mixed[]
	 */
	private function validateReservation($reservation, $game_type_id) {
		if (Views\ReservationExtended::isEventOn($this->pdo, $reservation->reservation_date)) {
			return ['result' => false, 'message' =>
				sprtintf('V den %s je naplánovaná událost a nelze tedy přidat %s.', date(DatetimeManager::HUMAN_DATE_ONLY, strtotime($reservation->reservation_date)), $reservation->isEvent() ? 'událost' : 'rezervaci')];
		}

		if ($reservation->isEvent()) {
			$count = Views\ReservationExtended::countReservationsOn($this->pdo, $reservation->reservation_date);
			if (!$count) {
				return ['result' => true];
			} else {
				return ['result' => false, 'message' =>
					sprtintf('V den %s není možné vytvořit událost, vytvoření blokuje %d %s', date(DatetimeManager::HUMAN_DATE_ONLY, strtotime($reservation->reservation_date)), $count, $count > 5 ? 'rezervací' : 'rezervace')];
			}
		}

		if ($reservation->desk_id != Tables\Desk::NO_DESK) {
			echo $reservation->desk_id;
			if (Views\ReservationExtended::checkDeskAvailable($this->reservation_date, $this->time_from, $this->time_tom)) {
				return ['result' => false, 'message' => sprintf("Stůl č %02d je ve vámi zvolený čas obsazený", $this->desk_id)];
			}
		}
		$box = Views\ReservationExtended::getAvailableGameBox($this->pdo, $game_type_id, $reservation->reservation_date);
		if (!$box) {
			return ['result' => false, 'message' => "Ve vámi zvolený čas není dostupná žádná herní krabice požadované hry."];
		}
		$reservation->game_box_id = $box;
	}

}
