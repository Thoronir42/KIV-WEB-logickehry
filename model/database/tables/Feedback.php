<?php

namespace model\database\tables;
use model\database\DB_Entity;
use model\services\DB_Service;

use libs\DatetimeManager;
/**
 * Description of User
 *
 * @author Stepan
 */
class Feedback extends DB_Entity {

	const TYPE_BUG = 1;
	const TYPE_SUGGESTION = 2;

	/**
	 * 
	 * @return Feedback
	 */
	public static function fromPOST() {
		$feedback = parent::createFromPost(self::class);
		switch ($feedback->feedback_type) {
			case self::TYPE_BUG: case self::TYPE_SUGGESTION:
				break;
			default:
				$feedback->feedback_type = self::TYPE_BUG;
		}
		return $feedback;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param mixed[] $pars
	 */
	public static function insert($pdo, $pars) {
		$statement = $pdo->prepare('INSERT INTO `web_logickehry_db`.`feedback` '
				. '(`feedback_type`, `user_id`, `label`, `description`, `created`) '
				. 'VALUES (:feedback_type, :user_id, :label, :description, :created);');
		if (!$statement->execute($pars)) {
			DB_Service::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString, $pars);
			return false;
		}
		return true;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $feedback_id
	 */
	public static function close($pdo, $feedback_id) {
		$statement = $pdo->prepare('UPDATE `web_logickehry_db`.`feedback` '
				. 'SET resolved = NOW() '
				. 'WHERE feedback_id = :fid');
		if (!$statement->execute(['fid' => $feedback_id])) {
			DB_Service::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString);
			return false;
		}
		return true;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $feedback_id
	 */
	public static function open($pdo, $feedback_id) {
		$statement = $pdo->prepare('UPDATE `web_logickehry_db`.`feedback` '
				. 'SET resolved = NULL '
				. 'WHERE feedback_id = :fid');
		if (!$statement->execute(['fid' => $feedback_id])) {
			DB_Service::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString);
			return false;
		}
		return true;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @return Feedback
	 */
	public static function fetchAll($pdo, $status = null) {
		$sql = "SELECT * FROM feedback";
		$pars = [];
		if (!is_null($status)) {
			$sql .= " WHERE resolved IS" . ($status ? '' : ' NOT') . " NULL";
			$pars['status'] = $status;
		}


		$statement = $pdo->prepare($sql);
		if (!$statement->execute($pars)) {
			DB_Service::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString, $pars);
			return null;
		}
		return $statement->fetchAll(\PDO::FETCH_CLASS, Feedback::class);
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @return Feedback
	 */
	public static function fetchById($pdo, $id) {
		$statement = $pdo->prepare("SELECT * FROM feedback "
				. "WHERE feedback_id = :id");
		if (!$statement->execute(['id' => $id])) {
			DB_Service::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString);
			return null;
		}
		return $statement->fetch(\PDO::FETCH_CLASS, Feedback::class);
	}

	public function isBugReport() {
		return $this->feedback_type == self::TYPE_BUG;
	}

	public function isFeatureSuggestion() {
		return $this->feedback_type == self::TYPE_SUGGESTION;
	}

	public function isResolved() {
		return !is_null($this->resolved);
	}

	public function getDate($type) {
		switch ($type) {
			default: return 'date error';
			case 'created':
			case 'resolved':
				return date(DatetimeManager::HUMAN_FULL, strtotime($this->$type));
		}
	}

	var $feedback_id;
	var $feedback_type;
	var $user_id;
	var $label;
	var $description;
	var $created;
	var $resolved;

}
