<?php
namespace model\database\tables;

/**
 * Description of User
 *
 * @author Stepan
 */
class User extends \model\database\DB_Entity{
	
	static $roles;
	
	var $user_id;
	
	var $orion_login;
	
	var $name;
	
	var $surname;
	
	var $role;
	
	/**
	 * 
	 * @return boolean
	 */
	public function isSupervisor(){
		return true;
	}
	
}
