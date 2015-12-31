<?php
namespace model\database\tables;

/**
 * Description of User
 *
 * @author Stepan
 */
class User extends \model\database\DB_Entity{
	
	const ROLE_USER = 1;
	const ROLE_SUPERVISOR = 2;
	const ROLE_ADMIN = 3;
	
	const MIN_NAME_LENGTH = 3;
	
	/**
	 * 
	 * @param \PDO $pdo
	 * @param String $orion_login
	 * @return boolean
	 */
	public static function insert($pdo, $orion_login){
		$statement = $pdo->prepare(
			"INSERT INTO `web_logickehry_db`.`user` (`orion_login`) VALUES (:ol)");
		return ($statement->execute(['ol' => $orion_login]));
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
				. "`name` = :name, "
				. "`surname` = :surname "
				. "WHERE `user`.`orion_login` = :orion_login"
				);
		return $statement->execute($pars);
	}
	
	/**
	 * 
	 * @param \PDO $pdo
	 * @param String $orion_login
	 * @param DateTime $time
	 * @return boolean
	 */
	public static function updateActivity($pdo, $orion_login, $time){
		
		$statement = $pdo->prepare(
			"UPDATE `web_logickehry_db`.`user` SET "
				. "`last_active` = :time "
				. "WHERE `user`.`orion_login` = :orion_login"
				);
		return $statement->execute(['time' => $time, 'orion_login' => $orion_login]);
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
		if($statement->execute(['role' => $role_id, 'orion_login' => $orion_login])){
			return true;
		}
		var_dump($statement->errorInfo());
		die;
	}
	
	public static function fromPOST(){ 
		$instance = parent::fromPOST(self::class);
		$instance->orion_login = filter_input(INPUT_SESSION, "orion_login");
		return $instance;
		
	}
	
	var $user_id;
	
	var $orion_login;
	
	var $name;
	
	var $surname;
	
	var $role_id;
	
	var $last_active;
	
	
	public function isSupervisor(){
		return $this->role_id >= self::ROLE_SUPERVISOR;
	}
	
	public function isAdministrator() {
		return $this->role_id >= self::ROLE_ADMIN;
	}
	
	public function isReady(){
		$params = ['name' => self::MIN_NAME_LENGTH, 'surname' => self::MIN_NAME_LENGTH];
		foreach($params as $par => $length){
			if(strlen($this->$par) < $length){ return false; }
		}
		return true;
	}
	
	public function isLoggedIn(){
		return (strlen($this->orion_login) > 2);
	}
	
	public function __sleep() {
		$ret = [];
		$ret[] = 'orion_login';
		return $ret;
	}
	
	public function getFullName(){
		if(strlen($this->name) >= self::MIN_NAME_LENGTH || strlen($this->surname) >= self::MIN_NAME_LENGTH){
			return "$this->name $this->surname";
		}
		return $this->orion_login;
	}
}
