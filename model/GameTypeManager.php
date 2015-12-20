<?php
namespace model;

use model\database\views\GameTypeExtended;

/**
 * Description of GameTypeManager
 *
 * @author Stepan
 */
class GameTypeManager {
	
	
	/**
	 * 
	 * @param \libs\PDOwrapper $pw
	 * @param mixed[] $pars
	 */
	public static function insert($pw, $pars) {
		$statement = $pw->con->prepare("INSERT INTO `web_logickehry_db`.`game_type` "
			. "(`game_type_id`, `game_name`, `subtitle`, `avg_playtime`, `max_players`, `min_players`) "
	. "VALUES ( :game_type_id,  :game_name,  :subtitle,  :avg_playtime,  :max_players,  :min_players )");
		if($statement->execute($pars)){
			return true;
		}
		var_dump($statement->errorInfo());
		echo '<br/>';
		var_dump($statement->queryString);
	}
	
	
	/**
	 * 
	 * @return GameTypeExtended[]
	 */
	public static function fetchAll($pw){
		$result = $pw->con->query("SELECT * FROM `game_type_extended`")
				->fetchAll(\PDO::FETCH_CLASS, GameTypeExtended::class);
		return $result;
	}
	
	/**
	 * 
	 * @param int $game_id
	 * @return GameTypeExtended
	 */
	public static function fetchById($game_id) {
		$statement = $this->con->prepare("SELECT * FROM game_type_extended WHERE game_type_id = :id");
		if($statement->execute(['id' => $game_id])){
			$result = $statement->fetchObject(GameTypeExtended::class);
			return $result;
		}
		return null;
	}
	
	public static function nextId($pw){
		 $result = $pw->con->query("SELECT game_type_id FROM game_type "
				 . "ORDER BY game_type_id DESC")->fetchColumn();
		 return $result + 1;
	}
}
