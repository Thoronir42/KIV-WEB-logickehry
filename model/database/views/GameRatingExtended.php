<?php

namespace model\database\views;

use config\Config;

use model\services\DB_Service;

use \model\database\tables\GameRating;

/**
 * Description of GameRatingExtended
 *
 * @author Stepan
 */
class GameRatingExtended extends GameRating {

	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $user_id
	 * @param int $game_type_id
	 * @return GameRatingExtended
	 */
	public static function fetchOne($pdo, $user_id, $game_type_id) {
		$statement = $pdo->prepare("SELECT * FROM `web_logickehry_db`.`game_rating_extended` "
				. "WHERE `game_type_id` = :gid AND `user_id` = :uid;");
		if (!$statement->execute(['gid' => $game_type_id, 'uid' => $user_id])) {
			DB_Service::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString);
			return true;
		}
		return $statement->fetchObject(self::class);
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $user_id
	 * @return GameRatingExtended[]
	 */
	public static function fetchAllByUser($pdo, $user_id) {
		$statement = $pdo->prepare("SELECT * FROM `web_logickehry_db`.`game_rating_extended` "
				. "WHERE `user_id` = :uid;");
		if (!$statement->execute(['uid' => $user_id])) {
			DB_Service::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString);
			return false;
		}
		return $statement->fetchAll(\PDO::FETCH_CLASS, self::class);
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $game_type_id
	 * @return GameRatingExtended[]
	 */
	public static function fetchAllByGameType($pdo, $game_type_id) {
		$statement = $pdo->prepare("SELECT * FROM `web_logickehry_db`.`game_rating_extended` "
				. "WHERE `game_type_id` = :gid;");
		if (!$statement->execute(['gid' => $game_type_id])) {
			DB_Service::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString);
			return false;
		}
		return $statement->fetchAll(\PDO::FETCH_CLASS, self::class);
	}

	var $nickname;
	var $orion_login;
	var $game_name;
	var $game_subtitle;

	public function userHasNickname() {
		return (strlen($this->nickname) >= Config::USER_NICKNAME_MIN_LENGTH);
	}

	public function getFullUserName() {
		if (strlen($this->nickname) >= Config::USER_NICKNAME_MIN_LENGTH) {
			return $this->nickname;
		}
		return $this->orion_login;
	}

}
