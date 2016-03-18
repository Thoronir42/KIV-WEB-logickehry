<?php

namespace model\database\tables;

use config\Config;
use model\database\DB_Entity;

/**
 * Description of User
 *
 * @author Stepan
 */
class User extends DB_Entity {

	const ROLE_USER = 1;
	const ROLE_SUPERVISOR = 2;
	const ROLE_ADMIN = 3;

	/**
	 * 
	 * @param \PDO $pdo
	 * @param String $orion_login
	 * @return boolean
	 */
	public static function insert($pdo, $orion_login) {
		$statement = $pdo->prepare(
				"INSERT INTO `web_logickehry_db`.`user` (`orion_login`) VALUES (:ol)");
		if(!$statement->execute(['ol' => $orion_login])){
			DB_Entity::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString);
			return false;
		}
		return true;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param mixed[] $pars
	 * @return boolean
	 */
	public static function update($pdo, $pars) {
		$statement = $pdo->prepare(
				"UPDATE `web_logickehry_db`.`user` SET "
				. "`nickname` = :nickname "
				. "WHERE `user`.`orion_login` = :orion_login"
		);
		if(!$statement->execute($pars)){
			DB_Entity::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString);
			return false;
		}
		return true;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 */
	public static function count($pdo) {
		$result = $pdo->query('SELECT count(orion_login) as count FROM `web_logickehry_db`.`user`');
		return $result->fetch(\PDO::FETCH_ASSOC)['count'];
	}

	/**
	 * 
	 * @param \PDO $pdo
	 */
	public static function fetchAllLogins($pdo) {
		$result = $pdo->query('SELECT orion_login FROM user');
		return $result->fetchAll(\PDO::FETCH_COLUMN);
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param String $orion_login
	 * @param DateTime $time
	 * @return boolean
	 */
	public static function updateActivity($pdo, $orion_login, $time) {

		$statement = $pdo->prepare(
				"UPDATE `web_logickehry_db`.`user` SET "
				. "`last_active` = :time "
				. "WHERE `user`.`orion_login` = :orion_login"
		);
		if(!$statement->execute(['time' => $time, 'orion_login' => $orion_login])){
			DB_Entity::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString);
			return false;
		}
		return true;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param String $orion_login
	 * @param int $role_id
	 * @return boolean
	 */
	public static function setUserRole($pdo, $orion_login, $role_id) {
		$statement = $pdo->prepare(
				"UPDATE `web_logickehry_db`.`user` SET "
				. "`role_id` = :role "
				. "WHERE `user`.`orion_login` = :orion_login"
		);
		if (!$statement->execute(['role' => $role_id, 'orion_login' => $orion_login])) {
			DB_Entity::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString);
			return false;
		}
		return true;
	}

	public static function fromPOST() {
		$instance = parent::fromPOST(self::class);
		$instance->orion_login = filter_input(INPUT_SESSION, "orion_login");
		return $instance;
	}

	var $user_id;
	var $orion_login;
	var $nickname;
	var $role_id;
	var $last_active;

	public function isSupervisor() {
		return $this->role_id >= self::ROLE_SUPERVISOR;
	}

	public function isAdministrator() {
		return $this->role_id >= self::ROLE_ADMIN;
	}

	public function hasNickname() {
		return (strlen($this->nickname) >= Config::USER_NICKNAME_MIN_LENGTH);
	}

	public function isLoggedIn() {
		return (!empty($this->orion_login));
	}

	public function __sleep() {
		$ret = [];
		$ret[] = 'orion_login';
		return $ret;
	}

	public function getFullName() {
		if (strlen($this->nickname) >= Config::USER_NICKNAME_MIN_LENGTH) {
			return $this->nickname;
		}
		return $this->orion_login;
	}

}
