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
	public function fetchWithinTimespan($pars) {
		$sql = "SELECT * FROM `event` "
				. "WHERE event_date >= :time_from AND event_date < :time_to "
				. "ORDER BY time_from ASC";
		$statement = $this->pdo->prepare($sql);
		if (!$statement->execute($pars)) {
			DB_Service::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString, $pars);
			return null;
		}
		return $statement->fetchAll(\PDO::FETCH_CLASS, Event::class);
	}

}
