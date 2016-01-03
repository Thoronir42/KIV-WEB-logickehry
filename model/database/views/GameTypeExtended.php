<?php

namespace model\database\views;

use \model\database\tables\GameType;

/**
 * Description of GamyTypeWithScore
 *
 * @author Stepan
 */
class GameTypeExtended extends GameType {

	/**
	 * 
	 * @param \PDO $pdo
	 * @return GameTypeExtended
	 */
	public static function fetchAll($pdo) {
		$result = $pdo->query("SELECT * FROM `game_type_extended`")
				->fetchAll(\PDO::FETCH_CLASS, GameTypeExtended::class);
		$games = [];
		foreach ($result as $r) {
			$games[$r->game_type_id] = $r;
		}
		return $games;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $game_id
	 * @return GameTypeExtended
	 */
	public static function fetchById($pdo, $game_id) {
		$statement = $pdo->prepare("SELECT * FROM game_type_extended WHERE game_type_id = :id");
		if ($statement->execute(['id' => $game_id])) {
			$result = $statement->fetchObject(GameTypeExtended::class);
			return $result;
		}
		return null;
	}

	var $average_score;
	var $rating_count;
	var $subscribed_users;
	var $box_count;
	var $reservationCount;

}
