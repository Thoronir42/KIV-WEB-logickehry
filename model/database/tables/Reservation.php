<?php

namespace model\database\tables;

/**
 * Description of Reservation
 *
 * @author Stepan
 */
class Reservation extends \model\database\DB_Entity {

	const EARLY_RESERVATION = 7;
	const LATE_RESERVATION = 19;
	const WEEK_START_DAY = 1;
	const WEEK_END_DAY = 7;
	const RES_TYPE_OPEN = 1;
	const RES_TYPE_CLOSED = 2;
	const RES_TYPE_EVENT = 3;

	public static function getTypes($includeEvent = false) {
		$return = [
			['type' => self::RES_TYPE_OPEN, 'label' => 'Rezervace otevřená pro přihlášení ostatním členů'],
			['type' => self::RES_TYPE_CLOSED, 'label' => 'Pouze rezervace'],
		];
		if ($includeEvent) {
			$return[] = ['type' => self::RES_TYPE_EVENT, 'label' => 'Celodenní událost'];
		}
		return $return;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param Reservation $res
	 */
	public static function insert($pdo, $res) {
		$statement = $pdo->prepare("INSERT INTO `web_logickehry_db`.`reservation` "
				. "(`game_box_id`, `reservee_user_id`, `reservation_type_id`, `reservation_date`, `time_from`, `time_to`, `desk_id`) "
		. "VALUES ( :game_box_id , :reservee_user_id,  :reservation_type_id,  :reservation_date,  :time_from,  :time_to,  :desk_id )");
		$pars = [
			'game_box_id' => $res->game_box_id, 'reservee_user_id' => $res->reservee_user_id,
			'reservation_type_id' => $res->reservation_type_id, 'reservation_date' => $res->reservation_date,
			'reservation_date' => date(\model\DatetimeManager::DB_DATE_ONLY, strtotime($res->reservation_date)),
			'time_to' => date(\model\DatetimeManager::DB_TIME_ONLY, strtotime($res->time_to)),
			'time_from' => date(\model\DatetimeManager::DB_TIME_ONLY, strtotime($res->time_from)),
			'desk_id' => $res->desk_id];
		if (!$statement->execute($pars)) {
			var_dump($statement->errorInfo());
			return false;
		}
		return true;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $user_id
	 * @param int $reservation_id
	 */
	public static function deleteAttendee($pdo, $user_id, $reservation_id) {
		$statement = $pdo->prepare('DELETE FROM `web_logickehry_db`.`reservation_users` '
				. 'WHERE user_id = :uid AND reservation_id = :rid');
		if ($statement->execute(['uid' => $user_id, 'rid' => $reservation_id])) {
			return true;
		}
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $user_id
	 * @param int $reservation_id
	 */
	public static function insertAttendee($pdo, $user_id, $reservation_id) {
		$statement = $pdo->prepare('INSERT INTO `web_logickehry_db`.`reservation_users` '
				. '(`user_id`, `reservation_id`) VALUES ( :uid, :rid )');
		if ($statement->execute(['uid' => $user_id, 'rid' => $reservation_id])) {
			return true;
		}
	}

	/**
	 * 
	 * @return Reservation
	 */
	public static function fromPOST() {
		$reservation = parent::fromPOST(self::class);
		if ($reservation->desk_id == Desk::NO_DESK) {
			$reservation->desk_id = null;
		}
		$missing = $reservation->missing;
		switch ($reservation->reservation_type_id) {
			case self::RES_TYPE_OPEN: case self::RES_TYPE_CLOSED:
				if (empty($reservation->time_from)) {
					$missing[] = 'time_from';
				}
				if (empty($reservation->time_to)) {
					$missing[] = 'time_to';
				}
				break;
			case self::RES_TYPE_EVENT:
				$reservation->time_from = sprintf('%02d:00:00', self::EARLY_RESERVATION);
				$reservation->time_to = sprintf('%02d:00:00', self::LATE_RESERVATION);
				break;
		}
		$reservation->missing = $missing;
		return $reservation;
	}

	var $reservation_id = false;
	var $game_box_id = false;
	var $reservee_user_id = false;
	var $reservation_type_id;
	var $reservation_date;
	var $time_from = false;
	var $time_to = false;
	var $desk_id = false;

	public function readyForInsert() {
		return parent::readyForInsert();
	}

	public function checkRequiredProperties() {
		return parent::checkRequiredProperties(self::class);
	}

	public function isEvent() {
		return $this->reservation_type_id == self::RES_TYPE_EVENT;
	}

	public function isOpen() {
		return $this->reservation_type_id == self::RES_TYPE_OPEN;
	}

	public function hasDesk() {
		return !empty($this->desk_id);
	}
}
