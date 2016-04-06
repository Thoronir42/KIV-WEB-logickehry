<?php

namespace model\database\tables;

use model\database\DB_Entity;
use model\services\DB_Service;

use libs\DatetimeManager;

/**
 * Description of Reservation
 *
 * @author Stepan
 */
class Reservation extends DB_Entity {

	const EARLY_RESERVATION = 7;
	const LATE_RESERVATION = 20;
	const WEEK_START_DAY = 1;
	const WEEK_END_DAY = 7;
	const RES_TYPE_OPEN = 1;
	const RES_TYPE_CLOSED = 2;

	public static function getTypes() {
		$return = [
			['type' => self::RES_TYPE_OPEN, 'label' => 'Rezervace otevřená pro přihlášení ostatním členů'],
			['type' => self::RES_TYPE_CLOSED, 'label' => 'Pouze rezervace'],
		];
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
			'reservation_date' => date(DatetimeManager::DB_DATE_ONLY, strtotime($res->reservation_date)),
			'time_to' => date(DatetimeManager::DB_TIME_ONLY, strtotime($res->time_to)),
			'time_from' => date(DatetimeManager::DB_TIME_ONLY, strtotime($res->time_from)),
			'desk_id' => $res->desk_id];
		if (!$statement->execute($pars)) {
			DB_Service::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString, $pars);
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
		if (!$statement->execute(['uid' => $user_id, 'rid' => $reservation_id])) {
			DB_Service::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString);
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
	public static function insertAttendee($pdo, $user_id, $reservation_id) {
		$statement = $pdo->prepare('INSERT INTO `web_logickehry_db`.`reservation_users` '
				. '(`user_id`, `reservation_id`) VALUES ( :uid, :rid )');
		if (!$statement->execute(['uid' => $user_id, 'rid' => $reservation_id])) {
			DB_Service::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString);
			return false;
		}
		return true;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $id
	 */
	public static function fetchAttendees($pdo, $id) {
		$sql = "SELECT u.orion_login FROM `web_logickehry_db`.`reservation_users` ru "
				. "JOIN user u ON ru.user_id = u.user_id "
				. "WHERE ru.reservation_id = :rid";
		$statement = $pdo->prepare($sql);
		
		if(!$statement->execute(['rid' => $id])){
			DB_Service::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString);
			return false;
		}
		return $statement->fetchAll(\PDO::FETCH_COLUMN);
	}
	
	
	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $id
	 */
	public static function delete($pdo, $id) {
		$statement = $pdo->prepare("DELETE FROM reservation WHERE reservation_id = :rid");
		if(!$statement->execute(['rid' => $id])){
			DB_Service::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString);
			return false;
		}
		return true;
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
		return $reservation;
	}

	var $reservation_id = false;
	var $game_box_id = false;
	var $reservee_user_id = false;
	var $reservation_type_id;
	var $reservation_date;
	var $time_from;
	var $time_to;
	var $desk_id = false;

	public function readyForInsert() {
		return parent::readyForInsert();
	}

	public function checkRequiredProperties() {
		return parent::checkRequiredProperties(self::class);
	}

	public function isOpen() {
		return $this->reservation_type_id == self::RES_TYPE_OPEN;
	}

	public function hasDesk() {
		return !empty($this->desk_id);
	}
}
