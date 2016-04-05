<?php

namespace model\database\tables;

use \model\database\DB_Entity;
use model\services\DB_Service;
use \model\database\IRenderableWeekEntity;

use libs\DatetimeManager;

/**
 * Description of Reservation
 *
 * @author Stepan
 */
class Event extends DB_Entity implements IRenderableWeekEntity {

	const NO_GAME_TYPE_ID = 0;

	const TYPE = 'event';
	
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
			'event_date' => date(DatetimeManager::DB_DATE_ONLY, strtotime($evt->event_date)),
			'time_from' => date(DatetimeManager::DB_TIME_ONLY, strtotime($evt->time_from)),
			'time_to' => date(DatetimeManager::DB_TIME_ONLY, strtotime($evt->time_to))];
		if (!$statement->execute($pars)) {
			DB_Service::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString, $pars);
			return 0;
		}
		return $pdo->lastInsertId();
	}
	
	/**
	 * 
	 * @param \PDO $pdo
	 * @param mixed[] $pars
	 * @param int $id
	 */
	public static function update($pdo, $pars, $id){
		$sql = 'UPDATE `event` SET ';
		$i = 0;
		$len = count($pars);
		
		foreach($pars as $key => $value){
			if(is_null($value)){
				$sql .= sprintf('`%s` = NULL', $key);
				unset($pars[$key]);
			} else {
				$sql .= sprintf('`%s` = :%s', $key, $key);
			}
			if(++$i != $len){
				$sql.= ', ';
			}
		}
		
		$sql .= " WHERE `event_id` = :event_id;";
		
		$pars['event_id'] = $id;
		
		$statement = $pdo->prepare($sql);
		
		if(!$statement->execute($pars)){
			DB_Service::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString, $pars);
			return false;
		}
		return true;
	}
	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $id
	 */
	public static function delete($pdo, $id){
		$statement = $pdo->prepare('DELETE FROM `event` WHERE `event_id` = :event_id;');
		
		$pars = ['event_id' => $id];
		if(!$statement->execute($pars)){
			DB_Service::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString, $pars);
			return false;
		}
		
		return true;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $id
	 * 
	 * @return Event instance with specified id
	 */
	public static function fetchById($pdo, $id) {
		$statement = $pdo->prepare("SELECT * FROM `web_logickehry_db`.`event` "
				. "WHERE event_id = :id ");
		if (!$statement->execute(['id' => $id])) {
			DB_Service::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString);
			return null;
		}
		return $statement->fetchObject(Event::class);
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param Date $date
	 * @param Time $time_from
	 * @param Time $time_to
	 * @return boolean
	 */
	public static function existsDuring($pdo, $date, $time_from, $time_to) {
		$statement = $pdo->prepare('SELECT * FROM event'
				. ' WHERE event_date = :date AND ( '
				. ' ( time_from <= :time_from1 AND :time_from2 <= time_to ) OR'
				. ' ( time_from <= :time_to1   AND :time_to2   <= time_to )'
				. ')');
		
		$pars = ['date' => $date];
		$pars['time_from1'] = $pars['time_from2'] = $time_from;
		$pars['time_to1'] = $pars['time_to2'] = $time_to;
		if (!$statement->execute($pars)) {
			DB_Service::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString, $pars);
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
		if ($event->game_type_id == self::NO_GAME_TYPE_ID) {
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

	
	public function getID() {
		return $this->event_id;
	}
	
	public function getDate() {
		return $this->event_date;
	}

	public function getTimeFrom() {
		return $this->time_from;
	}

	public function getTimeLength() {
		return strtotime($this->getTimeTo()) - strtotime($this->getTimeFrom());
	}

	public function getTimeTo() {
		return $this->time_to;
	}

	public function hasSubtitle() {
		return !(empty($this->event_subtitle));
	}

	public function getSubtitle() {
		return $this->event_subtitle;
	}

	public function getTitle() {
		return $this->event_title;
	}

	public function getType() {
		return self::TYPE;
	}
	
	public function getLabel(){
		return 'Událost';
	}

	public function hasGameAssigned() {
		return $this->game_type_id != self::NO_GAME_TYPE_ID;
	}

	public function getGameTypeID() {
		return $this->hasGameAssigned() ? $this->game_type_id : self::NO_GAME_TYPE_ID;
	}

	public function isEvent() {
		return true;
	}

}
