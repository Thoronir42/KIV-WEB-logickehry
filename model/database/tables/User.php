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
	
	var $role;
	
	
	public function isSupervisor(){
		return true;
	}
	
	public function isReady(){
		$arr = (array)$this;
		foreach($arr as $prop){
			if(empty($prop)){ return false; }
		}
		return true;
	}
}
