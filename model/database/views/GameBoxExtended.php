<?php

namespace model\database\views;

use model\database\tables\GameBox;

/**
 * Description of GameBoxExtended
 *
 * @author Stepan
 */
class GameBoxExtended extends GameBox {

	/**
	 * 
	 * @param \PDO $pdo
	 * @param String $code
	 * @return GameBoxExtended
	 */
	public static function fetchByCode($pdo, $code) {
		$statement = $pdo->prepare("SELECT * FROM game_box_extended WHERE tracking_code = :code");
		if ($statement->execute(['code' => $code])) {
			$result = $statement->fetchObject(GameBoxExtended::class);
			return $result;
		}
		return null;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param boolean $includeRetired
	 * @return GameBoxExtended[]
	 */
	public static function fetchAll($pdo, $includeRetired = true) {
		$sql = "SELECT * FROM game_box_extended";
		if (!$includeRetired) {
			$sql .= " WHERE retired = 0";
		}
		$statement = $pdo->prepare($sql);
		if ($statement->execute()) {
			return $statement->fetchAll(\PDO::FETCH_CLASS, GameBoxExtended::class);
		}
		return null;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $game_type_id
	 * @return GameBoxExtended[]
	 */
	public static function fetchAllByGameType($pdo, $game_type_id) {
		$statement = $pdo->prepare("SELECT * FROM game_box_extended"
				. " WHERE retired = 0 "
				. "AND game_type_id = :gid "
				. "ORDER BY times_reserved ASC");
		if (!$statement->execute(['gid' => $game_type_id])) {
			return false;
		}
		$result =  $statement->fetchAll(\PDO::FETCH_CLASS, GameBoxExtended::class);
		$boxes = [];
		foreach($result as $gb){
			$boxes[$gb->game_box_id] = $gb;
		}
		return $boxes;
		
	}

	var $game_name;
	var $game_subtitle;
	var $times_reserved;
	

}
