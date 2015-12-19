<?php
namespace libs;

use PDO;
use model\database\views as Views;
use model\database\tables as Tables;


class PDOwrapper{
    /** @var PDO */
    private $con;
    
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
	
	public function getUsers(){
		$result = $this->con->query("SELECT * FROM user")
				->fetchAll(PDO::FETCH_CLASS, Tables\User::class);
		return $result;
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
	
	
	
	/**
	 * 
	 * @param type $withRetired
	 * @return Views\GameBoxExtended[]
	 */
	public function getGameBoxes(){
		$sql = "SELECT * FROM game_box_extended";
		$result = $this->con->query($sql)
				->fetchAll(PDO::FETCH_CLASS, Views\GameBoxExtended::class);
		return $result;
	}
	
	public function insertGameBox($pars) {
		$statement = $this->con->prepare("INSERT INTO `web_logickehry_db`.`game_box` "
			. "(`tracking_code`, `game_type_id`) "
	 ."VALUES ( :tracking_code,  :game_type_id);");
		if($statement->execute($pars)){ return true; } else {
			var_dump($statement->errorInfo());
			echo "<br>".$statement->queryString;
		}
	}
	
	/**
	 * 
	 * @param String $code
	 * @return Views\GameBoxExtended
	 */
	public function gameGameBoxByCode($code){
		$statement = $this->con->prepare("SELECT * FROM game_box_extended WHERE tracking_code = :code");
		if($statement->execute(['code' => $code])){
			$result = $statement->fetchObject(Views\GameBoxExtended::class);
			return $result;
		}
		return null;
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
	
	/**
	 * 
	 * @param \Tables\GameBox $code
	 * @return type
	 */
	public function fetchBox($code){
		$statement = $this->con->prepare("SELECT * FROM game_box
			WHERE tracking_code = :code");
		if($statement->execute(['code' => $code])){
			return $statement->fetchObject(Tables\GameBox::class);
		}
		return null;
	}

	public function fetchUser($orion_login) {
		$statement = $this->con->prepare("SELECT * FROM user_extended
			WHERE orion_login = :ol");
		if($statement->execute(['ol' => $orion_login])){
			return $statement->fetchObject(Views\UserExtended::class);
		}
		return null;
	}

	public function insertUser($orion_login) {
		$statement = $this->con->prepare(
			"INSERT INTO `web_logickehry_db`.`user` (`orion_login`) VALUES (:ol)");
		return ($statement->execute(['ol' => $orion_login]));
	}

	public function updateUser($pars) {
		$statement = $this->con->prepare(
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
		$statement = $this->con->prepare(
			"UPDATE `web_logickehry_db`.`game_box` SET "
				. "`retired` = 1 "
				. "WHERE `game_box`.`tracking_code` = :tracking_code"
				);
		if($statement->execute(['tracking_code' => $code])){
			return $box;
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