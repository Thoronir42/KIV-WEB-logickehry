<?php
namespace libs;

use PDO;
use model\database\views as Views;
use model\database\tables as Tables;


class PDOwrapper{
    /** @var PDO */
    private $connection;
    
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
		$this->connection = $pdo;
	}
	
	public function getGamesWithScores(){
		$result = $this->connection->query("SELECT * FROM `game_type_extended`")
				->fetchAll(PDO::FETCH_CLASS, Views\GameTypeExtended::class);
		return $result;
	}
	
	public function getFirstUnusedGameTypeId(){
		 $result = $this->connection->query("SELECT game_type_id FROM game_type "
				 . "ORDER BY game_type_id DESC")->fetchColumn();
		 return $result;
	}
	
	/**
	 * 
	 * @param type $pars
	 * @return Views\ReservationExtended[]
	 */
	public function getReservationsExtended($pars){
		$statement = $this->connection->prepare("SELECT * FROM `reservation_extended` "
				. "WHERE time_from > :time_from AND time_to < :time_to "
				. "ORDER BY time_from ASC");
		if($statement->execute($pars)){
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
	
	public function getUsers(){
		$result = $this->connection->query("SELECT * FROM user")
				->fetchAll(PDO::FETCH_CLASS, Tables\User::class);
		return $result;
	}
	public function getGameTypes(){
		$result = $this->connection->query("SELECT * FROM game_type")
				->fetchAll(PDO::FETCH_CLASS, Tables\GameType::class);
		return $result;
	}
	
	/**
	 * 
	 * @param type $withRetired
	 * @return Views\GameBoxExtended[]
	 */
	public function getGameBoxes(){
		$sql = "SELECT * FROM game_box_extended";
		$result = $this->connection->query($sql)
				->fetchAll(PDO::FETCH_CLASS, Views\GameBoxExtended::class);
		return $result;
	}
	
	public function getDesks(){
		$result = $this->connection->query("SELECT * FROM desk")
				->fetchAll(PDO::FETCH_CLASS, Tables\Desk::class);
		return $result;
	}

	/**
	 * @return Tables\GameType Typ hry id
	 */
	public function fetchGame($id) {
		if(!is_numeric($id)){ return null; }
		$statement = $this->connection->prepare("SELECT * FROM game_type"
				. "WHERE game_type_id == :game_type_id");
		if($statement->execute(["game_type_id" => $id])){
			return $statement->fetchObject(Tables\GameType::class);
		}
		return null;
	}
	
	/**
	 * 
	 * @param \Tables\GameBox $code
	 * @return type
	 */
	public function fetchBox($code){
		$statement = $this->connection->prepare("SELECT * FROM game_box
			WHERE tracking_code = :code");
		if($statement->execute(['code' => $code])){
			return $statement->fetchObject(Tables\GameBox::class);
		}
		return null;
	}

	public function fetchUser($orion_login) {
		$statement = $this->connection->prepare("SELECT * FROM user_extended
			WHERE orion_login = :ol");
		if($statement->execute(['ol' => $orion_login])){
			return $statement->fetchObject(Views\UserExtended::class);
		}
		return null;
	}

	public function insertUser($orion_login) {
		$statement = $this->connection->prepare(
			"INSERT INTO `web_logickehry_db`.`user` (`orion_login`) VALUES (:ol)");
		return ($statement->execute(['ol' => $orion_login]));
	}

	public function updateUser($pars) {
		$statement = $this->connection->prepare(
			"UPDATE `web_logickehry_db`.`user` SET "
				. "`name` = :name, "
				. "`surname` = :surname "
				. "WHERE `user`.`orion_login` = :orion_login"
				);
		return $statement->execute($pars);
	}

	public function retireBox($code) {
		$box = $this->fetchBox($code);
		if(!$box){ return null; }
		$statement = $this->connection->prepare(
			"UPDATE `web_logickehry_db`.`game_box` SET "
				. "`retired` = 1 "
				. "WHERE `game_box`.`tracking_code` = :tracking_code"
				);
		if($statement->execute(['tracking_code' => $code])){
			return $box;
		}
		return null;
	}

}