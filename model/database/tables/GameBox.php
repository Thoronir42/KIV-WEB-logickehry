<?php

namespace model\database\tables;

use model\database\DB_Entity;

/**
 * Description of GameBox
 *
 * @author Stepan
 */
class GameBox extends DB_Entity {

	const MIN_CODE_LENGTH = 5;

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
		if (!$statement->execute($pars)) {
			DB_Entity::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString);
			return false;
		}
		return true;
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
		if (!$statement->execute(['tracking_code' => $code])) {
			DB_Entity::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString);
			return null;
		}
		return $box;
	}

	var $game_box_id;
	var $game_type_id;
	var $tracking_code;
	var $add_note;
	var $retired;

}
