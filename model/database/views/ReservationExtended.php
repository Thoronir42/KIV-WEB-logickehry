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
				. "WHERE reservation_date >= :time_from AND reservation_date < :time_to "
				. "ORDER BY time_from ASC");
		if ($statement->execute($pars)) {
			return $statement->fetchAll(\PDO::FETCH_CLASS, ReservationExtended::class);
		}
		return null;
	}

	public static function countByGametypeWithinTimespan($pdo, $pars) {
		$statement = $pdo->prepare("SELECT game_type_id, count(reservation_id) as count FROM `reservation_extended` "
				. "WHERE reservation_date >= :time_from AND reservation_date <= :time_to "
				. "GROUP BY game_type_id");
		if ($statement->execute($pars)) {
			return $statement->fetchAll(\PDO::FETCH_ASSOC);
		}
		return null;
	}

	var $borrower_name;
	var $tracking_code;
	var $reservation_type;
	var $game_type_id;
	var $game_name;
	var $min_players;
	var $signed_players;
	var $max_players;
	var $desk_capacity;

}
