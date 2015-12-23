<?php
namespace model\database\tables;

/**
 * Description of User
 *
 * @author Stepan
 */
class User extends \model\database\DB_Entity{
	
	static $roles;
	
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
	
	
	public function isSupervisor(){
		return \model\UserManager::isSupervisor($this->role_id);
	}
	
	public function isAdministrator() {
		return \model\UserManager::isAdministrator($this->role_id);
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
