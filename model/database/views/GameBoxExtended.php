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
	 * @param String $code
	 * @return Views\GameBoxExtended
	 */
	public static function fetchByCode($pw, $code) {
		$statement = $pw->con->prepare("SELECT * FROM game_box_extended WHERE tracking_code = :code");
		if ($statement->execute(['code' => $code])) {
			$result = $statement->fetchObject(GameBoxExtended::class);
			return $result;
		}
		return null;
	}

	/**
	 * 
	 * @param \libs\PDOwrapper $pw
	 * @return GameBoxExtended[]
	 */
	public static function fetchAll($pw, $includeRetired = true) {
		$sql = "SELECT * FROM game_box_extended";
		if (!$includeRetired) {
			$sql .= " WHERE retired = 0";
		}
		$statement = $pw->con->prepare($sql);
		if ($statement->execute()) {
			return $statement->fetchAll(\PDO::FETCH_CLASS, GameBoxExtended::class);
		}
		return null;
	}

	var $game_name;
	var $picture_path;

}
