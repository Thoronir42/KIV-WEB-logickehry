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
		$this->template['resRend'] = new \model\ReservationRenderer(Tables\Reservation::EARLY_RESERVATION, Tables\Reservation::LATE_RESERVATION);
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

		$this->template['reservationFormAction'] = ['controller' => 'rezervace', 'action' => 'rezervovat'];
		$this->template['eventFormAction'] = ['controller' => 'rezervace', 'action' => 'vytvoritUdalost'];
		

		$rw = \model\ReservationManager::prepareReservationWeek($this->pdo, $week);
		$this->template["reservationDays"] = $rw['reservationDays'];
		$this->template["pageTitle"] = $rw['pageTitle'];
		$this->template["timeSpan"] = DatetimeManager::format($rw['timePars'], DatetimeManager::HUMAN_DATE_ONLY);
		
		$this->template["reservationTypes"] = Tables\Reservation::getTypes($this->user->isSupervisor());
		$this->template['games'] = $this->prepareGames($dbTimePars);
		$this->template['desks'] = Tables\Desk::fetchAll($this->pdo);
		$this->template['weekShift'] = $this->makeWeekLinks($week);
		$this->template['resListColSize'] = $this->colSizeFromGet();
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

	/**
	 * 
	 * @param mixed[] $timePars
	 * @return Views\GameTypeExtended[]
	 */
	private function prepareGames($timePars) {
		$games = Views\GameTypeExtended::fetchAllWithCounts($this->pdo);
		$resCounts = Views\ReservationExtended::countByGametypeWithinTimespan($this->pdo, $timePars);
		foreach ($resCounts as $count) {
			$games[$count['game_type_id']]->reservationCount = $count['count'];
		}
		return $games;
	}

	public function doRezervovat() {
		$game_type_id = $this->getParam('game_type_id', INPUT_POST);
		$reservation = \model\database\tables\Reservation::fromPOST();
		$reservation->reservee_user_id = $this->user->user_id;


		if (!$reservation->readyForInsert()) {
			$this->message('Vstupní pole rezervace nebyla správně vyplněna - rezervae nebyla přidána');
			$this->redirectPars('rezervace', 'vypis');
		}

		$v = $this->validateReservation($reservation, $game_type_id);
		if (!$v['result']) {
			$this->message($v['message'], \libs\MessageBuffer::LVL_WAR);
			$this->redirectPars('rezervace', 'vypis');
		}
		$reservation->game_box_id = $v['box']->game_box_id;
		if (!Tables\Reservation::insert($this->pdo, $reservation)) {
			$this->message('Při ukládání rezervace nastaly neočekávané potíže.', \libs\MessageBuffer::LVL_WAR);
		} else {
			$this->message('Rezervace byla úspěšně uložena.', \libs\MessageBuffer::LVL_SUC);
		}
		$this->redirectPars('rezervace');
	}
	
	/**
	 * 
	 * @param Tables\Reservation $reservation
	 * @return mixed[]
	 */
	private function validateReservation($reservation, $game_type_id) {
		$resCounts = Views\ReservationExtended::countReservationsOn($this->pdo, $reservation->reservation_date);

		if (!empty($resCounts) && !empty($resCounts[Tables\Reservation::RES_TYPE_EVENT])) {
			return ['result' => false, 'message' =>
				sprtintf('V den %s je naplánovaná událost a nelze tedy přidat %s.', date(DatetimeManager::HUMAN_DATE_ONLY, strtotime($reservation->reservation_date)), $reservation->isEvent() ? 'událost' : 'rezervaci')];
		}

		if ($reservation->isEvent()) {
			if (!empty($resCounts)) {
				$total = $resCounts['total'];
				return ['result' => false, 'message' =>
					\sprintf('V den %s není možné vytvořit událost, vytvoření blokuje %d %s', date(DatetimeManager::HUMAN_DATE_ONLY, strtotime($reservation->reservation_date)), $total, $total >= 5 ? 'rezervací' : 'rezervace')];
			}
		} else if ($reservation->desk_id != Tables\Desk::NO_DESK) {
			echo $reservation->desk_id;
			if (Views\ReservationExtended::checkDeskAvailable($this->pdo, $reservation->reservation_date, $reservation->time_from, $reservation->time_to)) {
				return ['result' => false, 'message' => \sprintf("Stůl č %02d je ve vámi zvolený čas obsazený", $reservation->desk_id)];
			}
		}
		$boxes = Views\ReservationExtended::getAvailableGameBox($this->pdo, $game_type_id, $reservation->reservation_date, $reservation->time_from, $reservation->time_to);

		if ($boxes === false) {
			return ['result' => false, 'message' => "Při kontrole použitých krabic nastala chyba."];
		}
		if (empty($boxes)) {
			return ['result' => false, 'message' => "Ve vámi zvolený čas není dostupná žádná herní krabice požadované hry."];
		}
		return ['result' => true, 'box' => array_shift(array_slice($boxes, 0, 1))];
	}

	public function renderDetail() {
		$id = $this->getParam('id');
		$reservation = $this->prepareReservation($id);
		if (empty($reservation)) {
			$this->message("Požadovaná rezeravce číslo $id není k dispozici.");
			$this->redirectPars('rezervace', 'vypis');
		}
		$this->template['r'] = $reservation;
		$curUserSigned = array_key_exists($this->user->user_id, $reservation->allUsers);
		$this->template['signAction'] = ['newVal' => !$curUserSigned,
			'url' => ['controller' => 'rezervace', 'action' => 'ucast', 'id' => $id, 'co' => $curUserSigned ? 'odhlasit' : 'prihlasit']];
	}

	private function prepareReservation($id) {
		$r = Views\ReservationExtended::fetchById($this->pdo, $id);
		if (empty($r)) {
			return null;
		}
		$r->user = Views\UserExtended::fetchById($this->pdo, $r->reservee_user_id);
		$r->game = Views\GameTypeExtended::fetchById($this->pdo, $r->game_type_id);
		$rUsers = [$r->user];
		$users = Views\ReservationExtended::getUsers($this->pdo, $r->reservation_id);
		foreach ($users as $u) {
			$rUsers[$u->user_id] = $u;
		}
		$r->allUsers = $rUsers;
		return $r;
	}

	public function doUcast() {
		$id = $this->getParam('id');
		$co = $this->getParam('co');
		switch ($co) {
			default:
				$result = ['result' => false, 'message' => "Neplatná operace účasti, jsou vaše odkazy akutální?"];
				break;
			case 'prihlasit':
				$result = $this->changeAttendancy($id, true);
				break;
			case 'odhlasit':
				$result = $this->changeAttendancy($id, false);
		}
		$this->message($result['message'], $result['result'] ? \libs\MessageBuffer::LVL_SUC : \libs\MessageBuffer::LVL_WAR);
		$this->redirectPars('rezervace', 'detail', ['id' => $id]);
	}

	private function changeAttendancy($reservation_id, $newVal) {
		$reservation = $this->prepareReservation($reservation_id);
		if (empty($reservation)) {
			return ['result' => true, 'message' => "Rezervace č $id není k dispozici a nelze u níměni vaší účast."];
		}
		$delOk = Tables\Reservation::deleteAttendee($this->pdo, $this->user->user_id, $reservation_id);
		if (!$newVal) {
			if ($delOk) {
				return ['result' => true, 'message' => 'Byli jste úspěšně odhlášeni z rezervace'];
			} else {
				return ['result' => false, 'message' => 'Při odhlašování z rezervace nastaly potíže'];
			}
		} else {
			$insOk = Tables\Reservation::insertAttendee($this->pdo, $this->user->user_id, $reservation_id);
			if ($insOk) {
				return ['result' => true, 'message' => 'Byli jste úspěšně přihlášeni z rezervace'];
			} else {
				return ['result' => false, 'message' => 'Při přihlašování k rezervaci nastaly potíže'];
			}
		}
	}

}
