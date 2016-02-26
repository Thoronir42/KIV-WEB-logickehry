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
	 * @param Event $evt
	 * 
	 * @return int id of inserted event
	 */
	public static function insert($pdo, $evt) {
		$statement = $pdo->prepare("INSERT INTO `web_logickehry_db`.`event`"
				. "       (`event_title`, `event_subtitle`, `description`, `game_type_id`, `author_user_id`, `event_date`, `time_from`, `time_to`) "
				. "VALUES (:event_title,  :event_subtitle,  :description,  :game_type_id,  :author_user_id,  :event_date,  :time_from,  :time_to )");
		$pars = [
			'event_title' => $evt->event_title, 'event_subtitle' => $evt->event_subtitle,
			'description' => $evt->description, 'game_type_id' => $evt->reservation_date,
			'author_user_id' => $evt->author_user_id,
			'event_date' => date(\model\DatetimeManager::DB_DATE_ONLY, strtotime($evt->event_date)),
			'time_from' => date(\model\DatetimeManager::DB_TIME_ONLY, strtotime($evt->time_from)),
			'time_to' => date(\model\DatetimeManager::DB_TIME_ONLY, strtotime($evt->time_to))];
		if (!$statement->execute($pars)) {
			var_dump($statement->errorInfo());
			return 0;
		}
		return $pdo->lastInsertId();
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $id
	 * 
	 * @return Event instance with specified id
	 */
	public static function fetchById($pdo, $id) {
		$result = $pdo->query("SELECT * FROM `web_logickehry_db`.`event`");
		return $result->fetchObject(Event::class);
	}
	
	/**
	 * 
	 * @param \PDO $pdo
	 * @param Date $date
	 * @param Time[] $time
	 * @return boolean
	 */
	public static function existsDuring($pdo, $date, $time) {
		$statement = $pdo->prepare('SELECT * FROM event '
				. 'reservation_date = :date AND ( '
				. ' ( time_from <= :time_from1 AND :time_from2 <= time_to ) OR'
				. ' ( time_from <= :time_to1   AND :time_to2   <= time_to )'
				. ')');
		$pars = ['date' => $date];
		$pars['time_from1'] = $pars['time_from2'] = $time['from'];
		$pars['time_to1'] = $pars['time_to2'] = $time['to'];
		if (!$statement->execute($pars)) {
			var_dump($statement->errorInfo());
			return false;
		}
		return(!empty($statement->fetchAll(\PDO::FETCH_CLASS, Event::class)));
	}

	/**
	 * 
	 * @return Event
	 */
	public static function fromPOST() {
		$event = parent::fromPOST(self::class);
		if(!$event->game_type_id){
			$event->game_type_id = NULL;
		}
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

	public function hasSubtitle(){
		return !(empty($this->event_subtitle));
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
