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

	/**
	 * 
	 * @param \PDO $pdo
	 * @param mixed[] $pars
	 * @return mixed[]
	 */
	public static function countByGametypeWithinTimespan($pdo, $pars) {
		$statement = $pdo->prepare("SELECT game_type_id, count(reservation_id) as count FROM `reservation_extended` "
				. "WHERE reservation_date >= :time_from AND reservation_date <= :time_to "
				. "GROUP BY game_type_id");
		if ($statement->execute($pars)) {
			return $statement->fetchAll(\PDO::FETCH_ASSOC);
		}
		return null;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $desk_id
	 * @param Date $date
	 * @param Time $time_from
	 * @param Time $time_tom
	 */
	public static function checkDeskAvailable($pdo, $desk_id, $date, $time_from, $time_tom) {
		$statement = $pdo->prepare('SELCT count(reservation_id) as count FROM reservation_extended '
				. 'WHERE desk_id = :desk_id AND '
				. 'reservation_date = :date AND ( '
				. '( date_to < :date_from && date_from > :date_from )'
				. '( date_to < :date_to && date_from > :date_to )'
				. ')');
		if ($statement->execute(['desk_id' => $desk_id, 'date' => $date, 'time_from' => $time_from, 'time_to' => $time_to])) {
			return $statement->fetch(\PDO::FETCH_COLUMN)['count'];
		}
		var_dump($statement->errorInfo());
		return false;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $game_type_id
	 * @param Date $date
	 * @param Time $time_from
	 * @param Time $time_to
	 * @return GameBoxExtended[]
	 */
	public static function getAvailableGameBox($pdo, $game_type_id, $date, $time_from = null, $time_to = null) {
		$boxes = GameBoxExtended::fetchAllByGameType($pdo, $game_type_id);

		$statement = $pdo->prepare('SELCT game_box_id FROM reservation_extended '
				. 'WHERE game_type_id = :game_type_id AND '
				. 'reservation_date = :date AND ( '
				.   '( date_to < :date_from && date_from > :date_from )'
				.   '( date_to < :date_to && date_from > :date_to )'
				. ')');
		if (!$statement->execute(['game_type_id' => $game_type_id, 'date' => $date, 'time_from' => $time_from, 'time_to' => $time_to])) {
			var_dump($statement->errorInfo());
			return false;
		}
		$boxesInUse = $statement->fetchAll(\PDO::FETCH_COLUMN)['game_box_id'];
		var_dump($boxesInUse); die;
		foreach($boxesInUse as $boi){
			
		}
		return $boxes;
	}

	public static function countReservationsOn($pdo, $date) {
		
	}

	public static function isEventOn($pdo, $date) {
		
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
