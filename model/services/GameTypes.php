<?php

namespace model\services;

use \PDO;

use model\database\tables\GameType;
use model\database\views\GameTypeExtended;

class GameTypes extends DB_Service {

	public function __construct(PDO $pdo) {
		parent::__construct($pdo);
	}

	/**
	 * 
	 * @param int $game_id
	 * @return GameTypeExtended
	 */
	public function fetchById($game_id) {
		$sql = "SELECT * FROM game_type_extended WHERE game_type_id = :id";
		$statement = $this->execute($sql, ['id' => $game_id]);
		if (!$statement) {
			return null;
		}
		return $statement->fetchObject(GameTypeExtended::class);
	}

}
