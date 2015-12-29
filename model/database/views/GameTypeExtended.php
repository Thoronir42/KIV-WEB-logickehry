<?php
namespace model\database\views;

use \model\database\tables\GameType;

/**
 * Description of GamyTypeWithScore
 *
 * @author Stepan
 */
class GameTypeExtended extends GameType{
	
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
	public static function fetchById($pw, $game_id) {
		$statement = $pw->con->prepare("SELECT * FROM game_type_extended WHERE game_type_id = :id");
		if($statement->execute(['id' => $game_id])){
			$result = $statement->fetchObject(GameTypeExtended::class);
			return $result;
		}
		return null;
	}
	
	var $average_score;
	
	var $rating_count;
	
	var $subscribed_users;
	
	var $box_count;
}
