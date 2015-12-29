<?php

namespace model\database\tables;

/**
 * Description of User
 *
 * @author Stepan
 */
class GameRating extends \model\database\DB_Entity {

	const SCORE_MIN = 1;
	const SCORE_DEF = 3;
	const SCORE_MAX = 5;

	public static function validate($val) {
		$return = ($val >= self::RATING_MAX) ? self::RATING_MAX :
				($val <= self::RATING_MIN ? self::RATING_MIN : round($val) );
		return intval($return);
	}

	/**
	 * 
	 * @param \libs\PDOwrapper $pw
	 * @param type $pars
	 */
	public static function insert($pw, $pars) {
		$statement = $pw->con->prepare("INSERT INTO `web_logickehry_db`.`game_rating` "
				. "(`game_type_id`, `user_id`, `score`, `review`) "
				. "VALUES (:game_type_id,  :user_id,  :score,  :review);");
		if ($statement->execute($pars)) {
			return true;
		}
		var_dump($statement->errorInfo());
		return false;
	}

	public static function delete($pw, $user_id, $game_type_id) {
		$statement = $pw->con->prepare("DELETE FROM `web_logickehry_db`.`game_rating` "
				. "WHERE `game_type_id` = :gid AND `user_id` = :uid;");
		if ($statement->execute(['gid' => $game_type_id, 'uid' => $user_id])) {
			return true;
		}
		var_dump($statement->errorInfo());
		return false;
	}

	public static function fromPOST() {
		return parent::fromPOST(self::class);
	}

	var $game_rating_id;
	var $game_type_id;
	var $score;
	var $review;

}
