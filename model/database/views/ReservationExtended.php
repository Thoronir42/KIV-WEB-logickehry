<?php

namespace model\database\views;

use \model\database\tables\Reservation;

/**
 * Description of GamyTypeWithScore
 *
 * @author Stepan
 */
class ReservationExtended extends Reservation {

	/**
	 * 
	 * @param \PDO $pdo
	 * @param mixed[] $pars
	 * @return ReservationExtended
	 */
	public static function fetchWithinTimespan($pdo, $pars) {
		$statement = $pdo->prepare("SELECT * FROM `reservation_extended` "
				. "WHERE time_from > :time_from AND time_to < :time_to "
				. "ORDER BY time_from ASC");
		if ($statement->execute($pars)) {
			return $statement->fetchAll(\PDO::FETCH_CLASS, ReservationExtended::class);
		}
		return null;
	}

	var $borrower_name;
	var $tracking_code;
	var $game_name;
	var $min_players;
	var $signed_players;
	var $max_players;
	var $desk_capacity;

}
