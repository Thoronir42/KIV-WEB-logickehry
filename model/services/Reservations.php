<?php

namespace model\services;

use \PDO;

use model\database\tables\Reservation;
use model\database\views\ReservationExtended;
/**
 * Description of Reservations
 *
 * @author Stepan
 */
class Reservations extends DB_Service {

	public function __construct(PDO $pdo) {
		parent::__construct($pdo);
	}

	/**
	 * 
	 * @param type $id
	 * @return ReservationExtended
	 */
	public function fetchById($id) {
		$statement = $this->pdo->prepare("SELECT * FROM `reservation_extended` "
				. "WHERE reservation_id = :id ");
		if (!$statement->execute(['id' => $id])) {
			DB_Service::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString);
			return null;
		}
		return $statement->fetchObject(ReservationExtended::class);
	}
	
	public function fetchWithin($date_from, $date_to, $user_id = null) {
		$sql = $user_id ?
				("SELECT reservation_extended.* FROM `reservation_extended` "
				. "LEFT JOIN reservation_users AS ru ON ru.reservation_id = reservation_extended.reservation_id "
				. "WHERE reservation_date >= :time_from AND reservation_date < :time_to "
				. "AND (reservation_extended.reservee_user_id = :uid1 OR ru.user_id = :uid2) "
				. "ORDER BY time_from ASC") :
				("SELECT * FROM `reservation_extended` "
				. "WHERE reservation_date >= :date_from AND reservation_date < :date_to "
				. "ORDER BY time_from ASC");
		$pars = ['date_from' => $date_from, 'date_to' => $date_to];
		if ($user_id) {
			$pars['uid1'] = $pars['uid2'] = $user_id;
		}
		$statement = $this->pdo->prepare($sql);
		if (!$statement->execute($pars)) {
			DB_Service::logError($statement->errorInfo(), __CLASS__ . "::" . __FUNCTION__, $statement->queryString, $pars);
			return null;
		}
		return $statement->fetchAll(\PDO::FETCH_CLASS, ReservationExtended::class);
	}

	public function countWithin($date_from, $date_to) {
		$statement = $this->pdo->prepare("SELECT game_type_id, count(reservation_id) as count FROM `reservation_extended` "
				. "WHERE reservation_type_id = :reservation_type_id AND :date_from <= reservation_date AND reservation_date <= :date_to "
				. "GROUP BY game_type_id");
		$pars = [
			'reservation_type_id' => Reservation::RES_TYPE_OPEN,
			'date_from' => $date_from,
			'date_to' => $date_to
				];
		
		if (!$statement->execute($pars)) {
			DB_Service::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString, $pars);
			return null;
		}
		return $statement->fetchAll(\PDO::FETCH_ASSOC);
	}

}
