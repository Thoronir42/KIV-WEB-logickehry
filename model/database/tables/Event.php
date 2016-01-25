<?php

namespace model\database\tables;

/**
 * Description of Reservation
 *
 * @author Stepan
 */
class Event extends \model\database\DB_Entity {

	/**
	 * 
	 * @param \PDO $pdo
	 * @param Reservation $res
	 */
	public static function insert($pdo, $res) {
		$statement = $pdo->prepare("INSERT INTO `web_logickehry_db`.`event` "
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
	 * @return Reservation
	 */
	public static function fromPOST() {
		$event = parent::fromPOST(self::class);
		return $event;
	}

	var $event_id = false;
	var $event_title;
	var $event_subtitle = false;
	var $description = false;
	var $reservation_date;
	var $game_type_id = false;

	public function checkRequiredProperties() {
		return parent::checkRequiredProperties(self::class);
	}

	public function getTime($type) {
		switch ($type) {
			case 'from':
				$time = $this->time_from;
				break;
			case 'to':
				$time = $this->time_to;
				break;
			default:
				return 'Nesprávný čas';
		}
		return date(\model\DatetimeManager::HUMAN_TIME_ONLY, strtotime($time));
	}

}
