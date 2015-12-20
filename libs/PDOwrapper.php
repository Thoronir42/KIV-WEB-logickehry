<?php
namespace libs;

use PDO;
use model\database\views as Views;
use model\database\tables as Tables;


class PDOwrapper{
    /** @var PDO */
    var $con;
    
	/**
	 * 
	 * @param array $cfg
	 * @return \PDOwrapper
	 */
    public static function getConnection($cfg){
        $cfg['password'] = isset($cfg['password']) ? $cfg['password'] : null;
        $pdo = new PDO("mysql:host=$cfg[host];dbname=$cfg[db_name];charset=utf8", $cfg['user'], $cfg['password']);
		return new PDOwrapper($pdo);
    }
	
	/**
	 * 
	 * @param PDO $pdo
	 */
	private function __construct($pdo) {
		$this->con = $pdo;
	}
	
	/**
	 * 
	 * @param type $pars
	 * @return Views\ReservationExtended[]
	 */
	public function getReservationsExtended($pars){
		$statement = $this->con->prepare("SELECT * FROM `reservation_extended` "
				. "WHERE time_from > :time_from AND time_to < :time_to "
				. "ORDER BY time_from ASC");
		if($statement->execute($pars)){
			return $statement->fetchAll(PDO::FETCH_CLASS, Views\ReservationExtended::class);
		}	
		return null;
	}
	
	public function gameRatingsByGameType($id) {
		$statement = $this->con->prepare("SELECT * FROM `game_rating_extended` "
				. "WHERE game_type_id = :id "
				. "ORDER BY time_from ASC");
		if($statement->execute(['id' => $id])){
			return $statement->fetchAll(PDO::FETCH_CLASS, Views\ReservationExtended::class);
		}
		return null;
	}
	
	public function insertReservation($pars){
		$pars['time_to'] = date(\model\DatetimeManager::DB_FORMAT, $pars['time_to']);
		$pars['time_to'] = date(\model\DatetimeManager::DB_FORMAT, $pars['time_to']);
		/*
		 INSERT INTO `web_logickehry_db`.`reservation`
		 * (`reservation_id`, `game_box_id`, `reservee_user_id`, `open_reservation`, `time_from`, `time_to`, `desk_id`)
		 *  VALUES (NULL, '3', '6', '1', '2015-12-14 12:23:00', '2015-12-14 14:00:00', '1');
		 */
	}
	
	public function getDesks(){
		$result = $this->con->query("SELECT * FROM desk")
				->fetchAll(PDO::FETCH_CLASS, Tables\Desk::class);
		return $result;
	}

	public function usersSubscribedGames($uid) {
		$statement = $this->con->prepare("SELECT game_type_id FROM subscription
			WHERE user_id = :uid");
		if($statement->execute(['uid' => $uid])){
			return $statement->fetchAll(PDO::FETCH_COLUMN);
		}
		return null;
	}
	
	public function subscribedUsersByGame($gid){
		$statement = $this->con->prepare("SELECT orion_login FROM subscribees "
				. "WHERE game_type_id = :gid");
		if($statement->execute(['gid' => $gid])){
			return $statement->fetchAll(PDO::FETCH_COLUMN);
		}
		return null;
	}

}