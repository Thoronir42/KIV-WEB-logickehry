<?php

namespace model\database\tables;

/**
 * Description of GameBox
 *
 * @author Stepan
 */
class GameBox extends \model\database\DB_Entity {

	/**
	 * 
	 * @param \PDO $pdo
	 * @param mixed[] $pars
	 * @return boolean
	 */
	public static function insert($pdo, $pars) {
		$statement = $pdo->prepare("INSERT INTO `web_logickehry_db`.`game_box` "
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
	 * @param \PDO $pdo
	 * @param String $code
	 * @return \model\database\views\GameBoxExtended
	 */
	public static function retire($pdo, $code) {
		$box = \model\database\views\GameBoxExtended::fetchByCode($pdo, $code);
		if (!$box) {
			return null;
		}
		$statement = $pdo->prepare(
				"UPDATE `web_logickehry_db`.`game_box` SET "
				. "`retired` = 1 "
				. "WHERE `game_box`.`tracking_code` = :tracking_code"
		);
		if ($statement->execute(['tracking_code' => $code])) {
			return $box;
		}
		return null;
	}

	var $game_box_id;
	var $game_type_id;
	var $tracking_code;
	var $add_note;
	var $retired;

}
