<?php

namespace model\database\tables;

/**
 * Description of User
 *
 * @author Stepan
 */
class Feedback extends \model\database\DB_Entity {

	/**
	 * 
	 * @param \PDO $pdo
	 * @return Feedback
	 */
	public static function fetchAll($pdo, $status = null) {
		$sql = "SELECT * FROM feedback";
		$pars = [];
		if (!is_null($status)) {
			$sql .= " WHERE resolved IS". ($status ?'':' NOT') ." NULL";
			$pars['status'] = $status;
		}


		$statement = $pdo->prepare($sql);
		if ($statement->execute($pars)) {
			return $statement->fetchAll(\PDO::FETCH_CLASS, Feedback::class);
		}
		return null;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @return Feedback
	 */
	public static function fetchById($pdo, $id) {
		$statement = $pdo->prepare("SELECT * FROM feedback "
				. "WHERE feedback_id = :id");
		if ($statement->execute(['id' => $id])) {
			$statement->fetch(\PDO::FETCH_CLASS, Feedback::class);
		}
		return $statement;
	}

	var $feedback_id;
	var $user_id;
	var $label;
	var $descripion;
	var $resolved;

}
