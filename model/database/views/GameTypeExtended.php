<?php

namespace model\database\views;

use \model\database\tables\GameType;
use model\database\DB_Entity;

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
		$result = $pdo->query("SELECT * FROM `game_type_extended` ORDER BY game_name")
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
	 * @return GameTypeExtended[]
	 */
	public static function fetchAllWithCounts($pdo) {
		$sql = "SELECT "
				. "game_type_extended.*, "
				. "COUNT(DISTINCT game_box.game_box_id) AS total_boxes, "
				. "COUNT(DISTINCT case game_box.retired when '0' then game_box.game_box_id else null end) AS active_boxes, "
				. "COUNT(DISTINCT reservation_extended.reservation_id) AS total_reservations "
				. " FROM `game_type_extended` "
				. "LEFT JOIN game_box "
				. "ON game_type_extended.game_type_id = game_box.game_type_id "
				. "LEFT JOIN reservation_extended "
				. "ON game_type_extended.game_type_id = reservation_extended.game_type_id "
				. "GROUP BY game_type_extended.game_type_id";
		$result = $pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
		$games = [];
		foreach ($result as $r) {
			$i = new GameTypeExtended();
			foreach($r as $k => $v){
				$i->$k = $v;
			}
			$games[$i->game_type_id] = $i;
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
		if (!$statement->execute(['id' => $game_id])) {
			DB_Entity::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString);
			return null;
		}
		return $statement->fetchObject(GameTypeExtended::class);
	}

	var $average_score;
	var $rating_count;
	var $subscribed_users;
	var $box_count;
	var $reservationCount;

	public function addTrackingCode($code){
		$this->misc['tracking_codes'][] = $code;
	}
}
