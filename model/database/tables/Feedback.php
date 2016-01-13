<?php

namespace model\database\tables;

/**
 * Description of User
 *
 * @author Stepan
 */
class Feedback extends \model\database\DB_Entity {

	const TYPE_BUG = 1;
	const TYPE_SUGGESTION = 2;

	/**
	 * 
	 * @return Feedback
	 */
	public static function fromPOST() {
		$feedback = parent::fromPOST(self::class);
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
		if($statement->execute($pars)){
			return true;
		}
		var_dump($pars);
		echo '<br>';
		var_dump($statement->errorInfo());
		return false;
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
	
	public function isBugReport(){
		return $this->feedback_type == self::TYPE_BUG;
	}
	public function isFeatureSuggestion(){
		return $this->feedback_type == self::TYPE_SUGGESTION;
	}

	var $feedback_id;
	var $feedback_type;
	var $user_id;
	var $label;
	var $description;
	var $created;
	var $resolved;

}
