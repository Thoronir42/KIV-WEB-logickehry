<?php
namespace model;

use model\database\views\UserExtended;

/**
 * Description of UserManager
 *
 * @author Stepan
 */
class UserManager {
	
	const LOGIN_SUCCESS = 1;
	const LOGIN_NEW = 2;
	const LOGIN_FAILED = 0;
	
	/**
	 * @param \libs\PDOwrapper $pw
	 * @return UserExtended
	 */
	public static function getCurrentUser($pw){
		if(!isset($_SESSION['user'])){ return new UserExtended(); }
		$orion_login = $_SESSION['user'];
		$dbUser =  $pw->fetchUser($orion_login);
		return $dbUser;
	}
	
	/**
	 * 
	 * @param \libs\PDOwrapper $pw
	 * @param String $orion_login
	 * 
	 * @return database\views\UserExtended
	 */
	public static function login($pw, $orion_login){
		$user = $pw->fetchUser($orion_login);
		if(!$user){
			if(!$this->pdoWrapper->insertUser($orion_login)){
				return null;
			} else {
				$user = $this->pdoWrapper->fetchUser($orion_login);
				$user->loginStatus = self::LOGIN_NEW;
			}
		} else {
			$user->loginStatus = self::LOGIN_SUCCESS;
		}
		$_SESSION['user'] = $orion_login;
		return $user;
	}

	public static function logout() {
		unset($_SESSION['user']);
		return true;
	}

	public static function update($pw, $pars) {
		return $this->pdoWrapper->updateUser($pars);
	}

}
