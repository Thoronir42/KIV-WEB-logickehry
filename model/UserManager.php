<?php

use model\database\views\UserExtended,
	libs\PDOwrapper;

/**
 * Description of UserManager
 *
 * @author Stepan
 */
class UserManager {
	
	/**
	 * 
	 * @return UserExtended
	 */
	public static function getCurrentUser(){
		//unset($_SESSION['user']);
		if(!isset($_SESSION['user'])){ return new UserExtended(); }
		$userSer = $_SESSION['user'];
		$user = unserialize($userSer);
		return $user;
	}
	
	/**
	 * 
	 * @param PDOwrapper $pdoWrapper
	 * @param String $orion_login
	 * 
	 * @return UserExtended
	 */
	public static function login($pdoWrapper, $orion_login){
		$user = $pdoWrapper->fetchUser($orion_login);
		if($user == null){
			$pdoWrapper->insertUser($orion_login);
		}
		$_SESSION['user'] = serialize($user);
		return $user;
	}
}
