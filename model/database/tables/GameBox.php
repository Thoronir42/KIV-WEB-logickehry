<?php
namespace model\database\tables;

/**
 * Description of GameBox
 *
 * @author Stepan
 */
class GameBox extends \model\database\DB_Entity{	
	
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
	
	var $game_box_id;
	
	var $game_type_id;
	
	var $tracking_code;
	
	var $add_note;
	
	var $retired;
	
}
