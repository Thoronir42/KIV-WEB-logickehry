<?php
namespace model;

use model\database\views\UserExtended;

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
		$dbUser =  self::fetch($pw, $orion_login);
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
		$user = self::fetch($pw, $orion_login);
		if(!$user){
			if(!self::insert($pw, $orion_login)){
				return null;
			} else {
				$user = self::fetch($pw, $orion_login);
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
		$statement = $pw->con->prepare(
			"UPDATE `web_logickehry_db`.`user` SET "
				. "`name` = :name, "
				. "`surname` = :surname "
				. "WHERE `user`.`orion_login` = :orion_login"
				);
		return $statement->execute($pars);
	}
	
	private static function fetch($pw, $orion_login){
		$statement = $pw->con->prepare("SELECT * FROM user_extended
			WHERE orion_login = :ol");
		if($statement->execute(['ol' => $orion_login])){
			return $statement->fetchObject(UserExtended::class);
		}
		return null;
	}
	
	private static function insert($pw, $orion_login){
		$statement = $pw->con->prepare(
			"INSERT INTO `web_logickehry_db`.`user` (`orion_login`) VALUES (:ol)");
		return ($statement->execute(['ol' => $orion_login]));
	}
	
	
	public function fetchAll($pw){
		$result = $pw->con->query("SELECT * FROM user_extended")
				->fetchAll(PDO::FETCH_CLASS, UserExtended::class);
		return $result;
	}
}
