<?php

namespace model\services;

use \PDO;

use model\database\tables\Event;

/**
 * Description of Reservations
 *
 * @author Stepan
 */
class Events extends DB_Service {

	public function __construct(PDO $pdo) {
		parent::__construct($pdo);
	}
	
	/**
	 * 
	 * @param mixed[] $pars
	 * @return Event[]
	 */
	public function fetchWithinTimespan($date_from, $date_to) {
		$sql = "SELECT * FROM `event` "
				. "WHERE event_date >= :date_from AND event_date <= :date_to "
				. "ORDER BY time_from ASC";
		$statement = $this->pdo->prepare($sql);
		if (!$statement->execute(['date_from' => $date_from, 'date_to' => $date_to])) {
			DB_Service::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString, $pars);
			return null;
		}
		return $statement->fetchAll(\PDO::FETCH_CLASS, Event::class);
	}

}
