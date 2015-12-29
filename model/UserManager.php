<?php
namespace model;

use model\database\tables\User,
	model\database\views\UserExtended;

/**
 * UserManager handles operations that regard currenly logged in user. 
 * Logged user's orion_login is stored in session global under 'user' key and
 * rest of the user data is fetched on each pageload.
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
		$dbUser = UserExtended::fetch($pw, $orion_login);
		if(!$dbUser){
			return new UserExtended();
		}
		
		$time = DatetimeManager::format(time(), DatetimeManager::DB_FORMAT);
		User::updateActivity($pw, $orion_login, $time);
		return $dbUser;
	}
	
	
	
	/**
	 * 
	 * @param \libs\PDOwrapper $pw
	 * @param String $orion_login
	 * 
	 * @return UserExtended
	 */
	public static function login($pw, $orion_login){
		$user = UserExtended::fetch($pw, $orion_login);
		if(!$user){
			if(!User::insert($pw, $orion_login)){
				return null;
			} else {
				$user = UserExtended::fetch($pw, $orion_login);
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
}
