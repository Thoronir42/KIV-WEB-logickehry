<?php

namespace model;

use model\database\views\GameBoxExtended;

/**
 * Description of GameBOxManager
 *
 * @author Stepan
 */
class GameBoxManager {

	public static function insert($pw, $pars) {
		$statement = $pw->con->prepare("INSERT INTO `web_logickehry_db`.`game_box` "
				. "(`tracking_code`, `game_type_id`) "
				. "VALUES ( :tracking_code,  :game_type_id);");
		if ($statement->execute($pars)) {
			return true;
		} else {
			var_dump($statement->errorInfo());
			echo "<br>" . $statement->queryString;
		}
	}

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

	public static function retire($pw, $code) {
		$box = self::fetchByCode($pw, $code);
		if (!$box) {
			return null;
		}
		$statement = $pw->con->prepare(
				"UPDATE `web_logickehry_db`.`game_box` SET "
				. "`retired` = 1 "
				. "WHERE `game_box`.`tracking_code` = :tracking_code"
		);
		if ($statement->execute(['tracking_code' => $code])) {
			return $box;
		}
		return null;
	}

	/**
	 * 
	 * @return GameBoxExtended[]
	 */
	public static function fetchAll($pw) {
		$sql = "SELECT * FROM game_box_extended";
		$result = $pw->con->query($sql)
				->fetchAll(\PDO::FETCH_CLASS, GameBoxExtended::class);
		return $result;
	}

}
