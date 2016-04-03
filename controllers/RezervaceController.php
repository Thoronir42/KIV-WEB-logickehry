<?php

namespace controllers;

use libs\DatetimeManager,
	libs\ReservationManager;
use libs\Mail\MailBuilder;
use \model\database\tables as Tables,
	\model\database\views as Views;

use model\services\Reservations;

class RezervaceController extends Controller {
	
	/** @var Reservations */
	private $reservaions;
	
	public function __construct($support) {
		parent::__construct($support);
		if (!$this->pdo) {
			return;
		}
		$this->reservaions = new Reservations($this->pdo);
	}

		
	public static function getDefaultAction() {
		return "vypis";
	}

	public function startUp() {
		parent::startUp();
		$this->template['resRend'] = \model\ReservationRenderer::getInstance();
		$this->addJs('moment-with-locales-min.js');
		$this->addJs('bootstrap-datetimepicker.js');
		$this->addCss('bootstrap-datetimepicker.min.css');
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

		$game_types = $this->prepareGames();

		$game_type_id = $this->getParam("game_id");
		if (Views\GameTypeExtended::fetchById($this->pdo, $game_type_id)) {
			$this->template['defaultGame'] = $game_type_id;
		}

		$this->template['reservationFormAction'] = ['controller' => 'rezervace', 'action' => 'rezervovat'];
		$this->template['eventFormAction'] = ['controller' => 'udalost', 'action' => 'pridat'];
		$this->template['eventGameList'] = Tables\Event::addNoGame($game_types);


		$rw = ReservationManager::prepareReservationWeek($this->pdo, $week);
		$this->template["reservationDays"] = $rw['reservationDays'];
		$this->template["pageTitle"] = $rw['pageTitle'];
		$this->template["timeSpan"] = DatetimeManager::format($rw['timePars'], DatetimeManager::HUMAN_DATE_ONLY);

		$this->template["reservationTypes"] = Tables\Reservation::getTypes();
		$this->template['games'] = $game_types;
		$this->template['desks'] = Tables\Desk::fetchAll($this->pdo);
		$this->template['weekShift'] = $this->makeWeekLinks($week);
		$this->template['resListColSize'] = $this->colSizeFromGet();

		$refill = $this->pickRefill();
		$this->template['refill'] = $refill;
		if ($this->user->isAdministrator()) {
			$this->template['switchButtons'] = [
				'res' => ['label' => 'rezervaci'],
				'evt' => ['label' => 'událost'],
			];
			$this->template['switchButtons'][$refill ? $refill['type'] : 'res']['active'] = true;
		}
	}

	private function pickRefill() {
		$detail = $this->getParam('detail');
		$arr = ['type' => 'none'];
		switch ($detail) {
			default:
				return null;
			case 'udalost':
				$arr['type'] = 'evt';
				$arr['evt'] = Tables\Event::fromPOST();
				break;
			case 'rezervace':
				$arr['type'] = 'res';
				$arr['res'] = Tables\Reservation::fromPOST();
				break;
		}
		return $arr;
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
	 * @return Views\GameTypeExtended[]
	 */
	private function prepareGames() {
		$games = Views\GameTypeExtended::fetchAllWithCounts($this->pdo);
		$date_from = DatetimeManager::format(strtotime('now'), DatetimeManager::DB_FULL);
		$date_to = DatetimeManager::format(strtotime('+ 1 month'), DatetimeManager::DB_FULL);
		
		$resCounts = $this->reservaions->countWithin($date_from, $date_to);
		
		foreach ($resCounts as $count) {
			$games[$count['game_type_id']]->reservationCount = $count['count'];
		}
		return $games;
	}

	public function doRezervovat() {
		$game_type_id = $this->getParam('game_type_id', INPUT_POST);
		$reservation = Tables\Reservation::fromPOST();
		$reservation->reservee_user_id = $this->user->user_id;


		if (!$reservation->readyForInsert()) {
			$this->message->warning('Vstupní pole rezervace nebyla správně vyplněna - rezervae nebyla přidána');
			$this->redirectPars('rezervace', 'vypis');
		}

		$v = $this->validateReservation($reservation, $game_type_id);
		if (!$v['result']) {
			$this->message->warning($v['message']);
			$this->redirectPars('rezervace', 'vypis');
		}
		$reservation->game_box_id = $v['box']->game_box_id;
		if (!Tables\Reservation::insert($this->pdo, $reservation)) {
			$this->message->warning('Při ukládání rezervace nastaly neočekávané potíže.');
		} else {
			$this->message->success('Rezervace byla úspěšně uložena.');
			if ($reservation->isOpen()) {
				$this->reservationCreatedSendMail($reservation->game_box_id);
			}
		}
		$this->redirectPars('rezervace');
	}

	public function doSmazat() {
		$id = $this->getParam('id');
		$reservation = $this->reservaions->fetchById($id);

		if (!$reservation) {
			$this->message->warning("Reservace nebyla nalezena.");
			$this->redirectPars("rezervace", $this->getDefaultAction());
		}

		if (!$this->user->isAdministrator() && $this->user->user_id != $reservation->reservee_user_id) {
			$this->message->warning("Pro odstranění rezervace nemáte dostatečná oprávnění");
			$this->redirectPars('rezervace', 'detail', ['id' => $id]);
		}

		// send mail?
		// $attendees = Tables\Reservation::fetchAttendees($this->pdo, $id);



		if (!Tables\Reservation::delete($this->pdo, $id)) {
			$this->message->warning("Při odstraňování rezervace nastaly neočekávané potíže.");
			$this->redirectPars('rezervace', 'detail', ['id' => $id]);
		}
		$this->message->info("Rezervae byla úspěšně odstraněna");
		$this->redirectPars("rezervace", $this->getDefaultAction());
	}

	/**
	 * 
	 * @param Tables\Reservation $reservation
	 * @return mixed[]
	 */
	private function validateReservation($reservation, $game_type_id) {
		$dateTime = [
			'date' => DatetimeManager::reformat($reservation->reservation_date, DatetimeManager::DB_DATE_ONLY),
			'time_from' => DatetimeManager::reformat($reservation->time_from, DatetimeManager::DB_TIME_ONLY),
			'time_to' => DatetimeManager::reformat($reservation->time_to, DatetimeManager::DB_TIME_ONLY),
		];
		$eventExists = Tables\Event::existsDuring($this->pdo, $dateTime['date'], $dateTime['time_from'], $dateTime['time_to']);
		if ($eventExists) {
			return ['result' => false, 'message' => sprtintf('Ve vámi zvolený čas je již naplánovaná událost a nelze tedy uložit rezervaci.')];
		}

		if ($reservation->desk_id != Tables\Desk::NO_DESK) {
			if (!Views\ReservationExtended::checkDeskAvailable($this->pdo, $reservation->desk_id, $dateTime['date'], $dateTime['time_from'], $dateTime['time_to'])) {
				return ['result' => false, 'message' => \sprintf("Stůl č %02d je ve vámi zvolený čas obsazený", $reservation->desk_id)];
			}
		}
		$boxes = Views\ReservationExtended::getAvailableGameBox($this->pdo, $game_type_id, $dateTime['date'], $dateTime['time_from'], $dateTime['time_from']);

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
			$this->message->warning("Požadovaná rezeravce číslo $id není k dispozici.");
			$this->redirectPars('rezervace', 'vypis');
		}
		$this->template['r'] = $reservation;
		$curUserSigned = array_key_exists($this->user->user_id, $reservation->allUsers);
		$this->template['signAction'] = ['newVal' => !$curUserSigned,
			'url' => ['controller' => 'rezervace', 'action' => 'ucast', 'id' => $id, 'co' => $curUserSigned ? 'odhlasit' : 'prihlasit']];
		if ($this->user->isAdministrator() || $this->user->user_id == $reservation->reservee_user_id) {
			$this->template['deleteLink'] = ['controller' => 'rezervace', 'action' => 'smazat', 'id' => $id];
		}
	}

	private function prepareReservation($id) {
		$reservation = $this->reservaions->fetchById($id);
		if (empty($reservation)) {
			return null;
		}
		$reservation->user = Views\UserExtended::fetchById($this->pdo, $reservation->reservee_user_id);
		$reservation->game = Views\GameTypeExtended::fetchById($this->pdo, $reservation->game_type_id);
		$rUsers = [$reservation->user];
		$users = Views\ReservationExtended::getUsers($this->pdo, $reservation->reservation_id);
		foreach ($users as $u) {
			$rUsers[$u->user_id] = $u;
		}
		$reservation->allUsers = $rUsers;
		return $reservation;
	}

	public function doUcast() {
		$id = $this->getParam('id');
		$co = $this->getParam('co');

		$reservation = $this->reservaions->fetchById($id);
		$reserveeUser = Views\UserExtended::fetchById($this->pdo, $reservation->reservee_user_id);

		switch ($co) {
			default:
				$result = ['result' => false, 'message' => "Neplatná operace účasti, jsou vaše odkazy akutální?"];
				break;
			case 'prihlasit':
				$result = $this->changeAttendancy($id, true);
				if ($result['result']) {
					$pars = [
						'userName' => $this->user->getFullName(),
						'reservationUrl' => $this->urlGen->url(['controller' => 'rezervace', 'action' => 'detail', 'id' => $id]),
					];
					MailBuilder::playerJoinedMyReservation($reserveeUser, $pars);
				}
				break;
			case 'odhlasit':
				$result = $this->changeAttendancy($id, false);
		}
		if ($result['result']) {
			$this->message->success($result['message']);
		} else {
			$this->message->warning($result['message']);
		}
		$this->redirectPars('rezervace', 'detail', ['id' => $id]);
	}

	private function changeAttendancy($reservation_id, $newVal) {
		$reservation = $this->prepareReservation($reservation_id);
		if (empty($reservation)) {
			return ['result' => true, 'message' => "Rezervace č $reservation_id není k dispozici a nelze u níměni vaší účast."];
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
				return ['result' => true, 'message' => 'Byli jste úspěšně přihlášeni k rezervaci'];
			} else {
				return ['result' => false, 'message' => 'Při přihlašování k rezervaci nastaly potíže'];
			}
		}
	}

	private function reservationCreatedSendMail($reservation_id) {
		$reservation = $this->reservaions->fetchById($reservation_id);
		$gameType = Views\GameTypeExtended::fetchById($this->pdo, $reservation->game_type_id);
		$users = Views\Subscription::fetchUsersByGame($this->pdo, $reservation->game_type_id);

		$dateTime = DatetimeManager::reformat($reservation->reservation_date, DatetimeManager::HUMAN_DATE_ONLY);
		$dateTime .= ' ' . DatetimeManager::reformat($reservation->time_from, DatetimeManager::HUMAN_TIME_ONLY);

		$pars = [
			'gameName' => $gameType->getFullName(),
			'reservationDate' => $dateTime,
			'reservationUrl' => $this->urlGen->url(['controller' => 'rezervace', 'action' => 'detail', 'id' => $reservation_id]),
		];
		MailBuilder::openReservationCreated($users, $pars);
	}

}
