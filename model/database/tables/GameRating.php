<?php

namespace model\database\tables;

use model\database\DB_Entity;

/**
 * Description of User
 *
 * @author Stepan
 */
class GameRating extends DB_Entity {

	const SCORE_MIN = 1;
	const SCORE_DEF = 3;
	const SCORE_MAX = 5;
	const MIN_REVIEW_LENGTH = 1;

	/**
	 * 
	 * @param numeric $val
	 * @return int
	 */
	public static function validate($val) {
		$return = ($val >= self::SCORE_MAX) ? self::SCORE_MAX :
				($val <= self::SCORE_MIN ? self::SCORE_MIN : round($val) );
		return intval($return);
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param mixed[] $pars
	 */
	public static function insert($pdo, $pars) {
		$statement = $pdo->prepare("INSERT INTO `web_logickehry_db`.`game_rating` "
				. "(`game_type_id`, `user_id`, `score`, `review`) "
				. "VALUES (:game_type_id,  :user_id,  :score,  :review);");
		if (!$statement->execute($pars)) {
			DB_Entity::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString, $pars);
			return false;
		}
		return true;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $user_id
	 * @param int $game_type_id
	 * @return boolean
	 */
	public static function delete($pdo, $user_id, $game_type_id) {
		$statement = $pdo->prepare("DELETE FROM `web_logickehry_db`.`game_rating` "
				. "WHERE `game_type_id` = :gid AND `user_id` = :uid;");
		if (!$statement->execute(['gid' => $game_type_id, 'uid' => $user_id])) {
			DB_Entity::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString);
			return false;
		}
		return true;
	}

	public static function fromPOST() {
		return parent::fromPOST(self::class);
	}

	var $game_rating_id;
	var $game_type_id;
	var $score;
	var $review;

	public function fullRating() {
		return ($this->hasScore() && $this->hasReview());
	}

	public function hasScore() {
		return !is_null($this->score);
	}

	public function hasReview() {
		return strlen($this->review) >= self::MIN_REVIEW_LENGTH;
	}

}
