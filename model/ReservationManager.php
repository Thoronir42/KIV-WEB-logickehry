<?php
namespace model;

use model\database\views\ReservationExtended;

/**
 * Description of ReservationManager
 *
 * @author Stepan
 */
class ReservationManager {
	
	const EARLY_RESERVATION = 7;
	const LATE_RESERVATION = 19;
	
	
	/**
	 * 
	 * @param type $pars
	 * @return ReservationExtended[]
	 */
	public static function fetchWithinTimespan($pw, $pars){
		$statement = $pw->con->prepare("SELECT * FROM `reservation_extended` "
				. "WHERE time_from > :time_from AND time_to < :time_to "
				. "ORDER BY time_from ASC");
		if($statement->execute($pars)){
			return $statement->fetchAll(\PDO::FETCH_CLASS, ReservationExtended::class);
		}
		return null;
	}
	/**
	 * 
	 * @param \libs\PDOwrapper $pw
	 * @param mixed[] $pars
	 */
	public static function insert($pw, $pars){
		$pars['time_to'] = date(\model\DatetimeManager::DB_FORMAT, $pars['time_to']);
		$pars['time_to'] = date(\model\DatetimeManager::DB_FORMAT, $pars['time_to']);
		$statement = $pw->con->prepare("INSERT INTO `web_logickehry_db`.`reservation` "
		. "(`reservation_id`, `game_box_id`, `reservee_user_id`, `open_reservation`, `time_from`, `time_to`, `desk_id`)
		VALUES (NULL,		  :game_box_id , :reservee_user_id, '1', '2015-12-14 12:23:00', '2015-12-14 14:00:00', '1'");
		if ($statement->execute($pars)){
			return true;
		}
		var_dump($statement->errorInfo());
		return false;
	}
}
