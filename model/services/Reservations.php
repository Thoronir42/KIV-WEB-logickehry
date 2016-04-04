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
			DB_Service::logError($statement->errorInfo(), __CLASS__ . "::" . __FUNCTION__, $statement->queryString);
			return null;
		}
		return $statement->fetchObject(ReservationExtended::class);
	}

	public function fetchWithin($date_from, $date_to, $user_id = null) {
		if($user_id){
			$query = $this->getFetchWithinSQL($date_from, $date_to, 'user', $user_id);
		} else {
			$query = $this->getFetchWithinSQL($date_from, $date_to);
		}
		
		$statement = $this->execute($query['sql'], $query['pars']);
		if (!$statement) {
			return null;
		}
		return $statement->fetchAll(\PDO::FETCH_CLASS, ReservationExtended::class);
	}

	public function fetchWithinByGame($date_from, $date_to, $gameTypeId) {
		$query = $this->getFetchWithinSQL($date_from, $date_to, 'game', $gameTypeId);
		$sql = $query['sql'];
		$pars = $query['pars'];
		
		$statement = $this->execute($sql, $pars);
		if (!$statement) {
			return null;
		}
		return $statement->fetchAll(\PDO::FETCH_CLASS, ReservationExtended::class);
	}

	private function getFetchWithinSQL($date_from, $date_to, $type = null, $value = null) {
		$sql = "SELECT reservation_extended.* FROM `reservation_extended` ";
		
		$timeSpanSql = "WHERE :date_from <= reservation_date AND reservation_date < :date_to ";
		$pars = ['date_from' => $date_from, 'date_to' => $date_to];
		switch ($type) {
			default:
				$sql .= $timeSpanSql;
				break;
			case 'game':
				$sql .= $timeSpanSql;
				$sql .= "AND reservation_extended.game_type_id= :gid AND reservation_extended.reservation_type_id = :type ";
				$pars['gid'] = $value;
				$pars['type'] = Reservation::RES_TYPE_OPEN;
				break;
			case 'user':
				$sql .= "LEFT JOIN reservation_users AS ru ON ru.reservation_id = reservation_extended.reservation_id ";
				$sql .= $timeSpanSql;
				$sql .= "AND (reservation_extended.reservee_user_id = :uid1 OR ru.user_id = :uid2) ";
				$pars['uid1'] = $pars['uid2'] = $value;
				break;
		}
		$sql .= "ORDER BY reservation_date ASC, time_from ASC";
		
		return ['sql' => $sql, 'pars' => $pars];
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
			DB_Service::logError($statement->errorInfo(), __CLASS__ . "::" . __FUNCTION__, $statement->queryString, $pars);
			return null;
		}
		return $statement->fetchAll(\PDO::FETCH_ASSOC);
	}

}
