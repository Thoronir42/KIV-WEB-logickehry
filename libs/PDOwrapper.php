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
	
	public function getReservationsExtended(){
		$result = $this->connection->query("SELECT * FROM `reservation_extended`")
				->fetchAll(PDO::FETCH_CLASS, Views\ReservationAndAll::class);
		return $result;
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
	
	public function getGameBoxes($withRetired = false){
		$sql = "SELECT * FROM game_box_extended";
		if(!$withRetired){
			$sql .= " WHERE retired = 0";
		}
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