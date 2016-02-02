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
	 * @return ReservationExtended[]
	 */
	public static function fetchWithinTimespan($pdo, $pars, $user_id = null) {
		$sql = $user_id ?
				("SELECT reservation_extended.* FROM `reservation_extended` "
				. "LEFT JOIN reservation_users AS ru ON ru.reservation_id = reservation_extended.reservation_id "
				. "WHERE reservation_date >= :time_from AND reservation_date < :time_to "
				. "AND (reservation_extended.reservee_user_id = :uid1 OR ru.user_id = :uid2) "
				. "ORDER BY time_from ASC")
				:
				("SELECT * FROM `reservation_extended` "
				. "WHERE reservation_date >= :time_from AND reservation_date < :time_to "
				. "ORDER BY time_from ASC");
		if($user_id){
			$pars['uid1'] = $pars['uid2'] = $user_id;
		}
		$statement = $pdo->prepare($sql);
		if ($statement->execute($pars)) {
			return $statement->fetchAll(\PDO::FETCH_CLASS, ReservationExtended::class);
		}
		return null;
	}

	public static function fetchWithinTimespanByUser($pdo, $pars, $user_id) {
		return self::fetchWithinTimespan($pdo, $pars, $user_id);
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param type $id
	 * @return ReservationExtended
	 */
	public static function fetchById($pdo, $id) {
		$statement = $pdo->prepare("SELECT * FROM `reservation_extended` "
				. "WHERE reservation_id = :id ");
		if ($statement->execute(['id' => $id])) {
			return $statement->fetchObject(ReservationExtended::class);
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
	 * @param Time $time_to
	 */
	public static function checkDeskAvailable($pdo, $desk_id, $date, $time_from, $time_to) {
		$statement = $pdo->prepare('SELCT count(reservation_id) as count FROM reservation_extended '
				. 'WHERE desk_id = :desk_id AND '
				. 'reservation_date = :date AND ( '
				. ' ( time_from <= :time_from1 AND :time_from2 <= time_to ) OR'
				. ' ( time_from <= :time_to1   AND :time_to2   <= time_to )'
				. ')');
		$pars = ['desk_id' => $desk_id, 'date' => $date,
			'time_from1' => $time_from, 'time_from2' => $time_from,
			'time_to1' => $time_to, 'time_to2' => $time_to];
		if ($statement->execute($pars)) {
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
	public static function getAvailableGameBox($pdo, $game_type_id, $date, $time_from, $time_to) {
		$boxes = GameBoxExtended::fetchAllByGameType($pdo, $game_type_id);

		$statement = $pdo->prepare('SELECT game_box_id FROM reservation_extended '
				. 'WHERE game_type_id = :game_type_id AND '
				. 'reservation_date = :date AND ( '
				. ' ( time_from <= :time_from1 AND :time_from2 <= time_to ) OR'
				. ' ( time_from <= :time_to1   AND :time_to2   <= time_to )'
				. ')');
		$pars = ['game_type_id' => $game_type_id, 'date' => $date,
			'time_from1' => $time_from, 'time_from2' => $time_from,
			'time_to1' => $time_to, 'time_to2' => $time_to];
		if (!$statement->execute($pars)) {
			var_dump($statement->errorInfo());
			return false;
		}
		$boxesInUse = $statement->fetchAll(\PDO::FETCH_COLUMN);
		foreach ($boxesInUse as $boi) {
			unset($boxes[$boi]);
		}
		return $boxes;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param Date $date
	 * @return mixed[]
	 */
	public static function countReservationsOn($pdo, $date) {
		$statement = $pdo->prepare('SELECT reservation_type_id, count(reservation_id) AS count FROM reservation_extended '
				. 'WHERE reservation_date = :date '
				. 'GROUP BY reservation_type_id');
		if (!$statement->execute(['date' => $date])) {
			return false;
		}
		$return = [];
		$result = $statement->fetchAll(\PDO::FETCH_ASSOC);
		$total = 0;
		foreach ($result as $r) {
			$total += ($return[$r['reservation_type_id']] = $r['count']);
		}
		if ($total > 0) {
			$return['total'] = $total;
		}
		return $return;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $reservation_id
	 * @return UserExtended[]
	 */
	public static function getUsers($pdo, $reservation_id) {
		$statement = $pdo->prepare('SELECT ue.* FROM reservation_users '
				. 'JOIN user_extended AS ue ON reservation_users.user_id = ue.user_id '
				. 'WHERE reservation_users.reservation_id = :rid');
		if (!$statement->execute(['rid' => $reservation_id])) {
			return false;
		}
		return $statement->fetchAll(\PDO::FETCH_CLASS, UserExtended::class);
	}

	var $reservation_type;
	var $signed_players;
	var $tracking_code;
	var $game_type_id;
	var $game_name;
	var $game_subtitle;
	var $min_players;
	var $max_players;
	var $desk_capacity;

	public function getSignedPlayerCount() {
		return $this->signed_players + 1;
	}

}
