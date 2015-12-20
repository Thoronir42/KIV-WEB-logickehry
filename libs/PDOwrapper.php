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
	 * @return Tables\GameType[]
	 */
	public function getGameTypesExtended(){
		$result = $this->con->query("SELECT * FROM `game_type_extended`")
				->fetchAll(PDO::FETCH_CLASS, Views\GameTypeExtended::class);
		return $result;
	}
	
	public function getFirstUnusedGameTypeId(){
		 $result = $this->con->query("SELECT game_type_id FROM game_type "
				 . "ORDER BY game_type_id DESC")->fetchColumn();
		 return $result + 1;
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
	
	/**
	 * 
	 * @param type $game_id
	 * @return Views\GameTypeExtended;
	 */
	public function gameTypeById($game_id) {
		$statement = $this->con->prepare("SELECT * FROM game_type_extended WHERE game_type_id = :id");
		if($statement->execute(['id' => $game_id])){
			$result = $statement->fetchObject(Views\GameTypeExtended::class);
			return $result;
		}
		return null;
	}
	
	public function getGameTypes(){
		$result = $this->con->query("SELECT * FROM game_type")
				->fetchAll(PDO::FETCH_CLASS, Tables\GameType::class);
		return $result;
	}
	/**
	 * 
	 * @param Tables\GameType $gameType
	 * @param int $game_type_id
	 */
	public function insertGameType($gameType, $game_type_id) {
		$statement = $this->con->prepare("INSERT INTO `web_logickehry_db`.`game_type` "
			. "(`game_type_id`, `game_name`, `subtitle`, `avg_playtime`, `max_players`, `min_players`) "
	 ."VALUES ( :game_type_id,  :game_name,  :subtitle,  :avg_playtime,  :max_players,  :min_players');");
		
		$pars = $gameType->asArray();
		$pars['game_type_id'] = $game_type_id;
		var_dump('insert', $pars, '<hr>');
		return ($statement->execute($pars));
	}
	
	
	
	
	
	
	
	
	
	public function getDesks(){
		$result = $this->con->query("SELECT * FROM desk")
				->fetchAll(PDO::FETCH_CLASS, Tables\Desk::class);
		return $result;
	}

	/**
	 * @return Tables\GameType Typ hry id
	 */
	public function fetchGame($id) {
		if(!is_numeric($id)){ return null; }
		$statement = $this->con->prepare("SELECT * FROM game_type"
				. "WHERE game_type_id == :game_type_id");
		if($statement->execute(["game_type_id" => $id])){
			return $statement->fetchObject(Tables\GameType::class);
		}
		return null;
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