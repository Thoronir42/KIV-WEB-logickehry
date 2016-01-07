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
	 * @param mixed[] $pars
	 */
	public static function insert($pdo, $pars) {
		$pars['time_to'] = date(\model\DatetimeManager::DB_FULL, $pars['time_to']);
		$pars['time_to'] = date(\model\DatetimeManager::DB_FULL, $pars['time_to']);
		$statement = $pdo->prepare("INSERT INTO `web_logickehry_db`.`reservation` "
				. "(`reservation_id`, `game_box_id`, `reservee_user_id`, `open_reservation`, `time_from`, `time_to`, `desk_id`)
		VALUES (NULL,		  :game_box_id , :reservee_user_id, '1', '2015-12-14 12:23:00', '2015-12-14 14:00:00', '1'");
		if ($statement->execute($pars)) {
			return true;
		}
		var_dump($statement->errorInfo());
		return false;
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
	
	public function isEvent(){
		return $this->reservation_type_id == self::RES_TYPE_EVENT;
	}
}
