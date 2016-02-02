<?php

namespace model\database\tables;

/**
 * Description of Reservation
 *
 * @author Stepan
 */
class Event extends \model\database\DB_Entity implements \model\database\IRenderableWeekEntity {

	const NO_GAME_TYPE_ID = 0;

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

	public static function addNoGame($game_types) {
		$noGame = new \model\database\views\GameTypeExtended();
		$noGame->game_name = "Žádná hra";
		$noGame->game_type_id = self::NO_GAME_TYPE_ID;

		return array_merge([$noGame], $game_types);
	}

	var $event_id = false;
	var $event_title;
	var $event_subtitle = false;
	var $description = false;
	var $event_date;
	var $time_from;
	var $time_to;
	var $game_type_id = false;
	var $author_user_id = false;

	public function checkRequiredProperties() {
		return parent::checkRequiredProperties(self::class);
	}

	///
	public function getDate() {
		return $this->event_date;
	}

	public function getTimeFrom() {
		return $this->time_from;
	}

	public function getTimeLength() {
		return $this->time_to - $this->time_from;
	}

	public function getTimeTo() {
		return $this->time_to;
	}

	public function getSubtitle() {
		return $this->event_subtitle;
	}

	public function getTitle() {
		return $this->event_title;
	}

	public function getType() {
		return "reservation";
	}

	public function hasGameAssigned() {
		return !$this->game_type_id;
	}

	public function getGameTypeID() {
		$this->game_type_id;
	}

	public function isEvent() {
		return true;
	}

}
