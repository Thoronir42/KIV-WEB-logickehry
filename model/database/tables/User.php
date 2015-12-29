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
	
	public static function insert($pw, $orion_login){
		$statement = $pw->con->prepare(
			"INSERT INTO `web_logickehry_db`.`user` (`orion_login`) VALUES (:ol)");
		return ($statement->execute(['ol' => $orion_login]));
	}
	
	public static function update($pw, $pars) {
		$statement = $pw->con->prepare(
			"UPDATE `web_logickehry_db`.`user` SET "
				. "`name` = :name, "
				. "`surname` = :surname "
				. "WHERE `user`.`orion_login` = :orion_login"
				);
		return $statement->execute($pars);
	}
	
	public static function updateActivity($pw, $orion_login, $time){
		
		$statement = $pw->con->prepare(
			"UPDATE `web_logickehry_db`.`user` SET "
				. "`last_active` = :time "
				. "WHERE `user`.`orion_login` = :orion_login"
				);
		return $statement->execute(['time' => $time, 'orion_login' => $orion_login]);
	}
	
	public static function addSupervisor($pdo, $orion_login) {
		return self::setUserRole($pdo, $orion_login, self::ROLE_SUPERVISOR);
	}

	public static function removeSupervisor($pdo, $orion_login) {
		return self::setUserRole($pdo, $orion_login, self::ROLE_USER);
	}
	
	private static function setUserRole($pdo, $orion_login, $role_id) {
		$statement = $pdo->con->prepare(
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
		$params = ['name' => 3, 'surname' => 3];
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
}
