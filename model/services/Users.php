<?php

namespace model\services;

use \PDO;

use libs\DatetimeManager;

use model\database\tables\User,
	model\database\views\UserExtended;

/**
 * UserManager handles operations that regard currenly logged in user. 
 * Logged user's orion_login is stored in session global under 'user' key and
 * rest of the user data is fetched on each pageload.
 * 
 * @author Stepan
 */
class Users extends DB_Service{

	const LOGIN_SUCCESS = 1;
	const LOGIN_NEW = 2;
	const LOGIN_FAILED = 0;

	public function __construct(PDO $pdo) {
		parent::__construct($pdo);
	}
	
	/**
	 * @param PDO $pdo
	 * @return UserExtended
	 */
	public function getCurrentUser() {
		if (!isset($_SESSION['user'])) {
			return new UserExtended();
		}
		$orion_login = $_SESSION['user'];
		$dbUser = UserExtended::fetch($this->pdo, $orion_login);
		if (!$dbUser) {
			return new UserExtended();
		}

		$time = DatetimeManager::format(time(), DatetimeManager::DB_FULL);
		User::updateActivity($this->pdo, $orion_login, $time);
		return $dbUser;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param String $orion_login
	 * 
	 * @return UserExtended
	 */
	public function login($orion_login) {
		$user = UserExtended::fetch($this->pdo, $orion_login);
		if (!$user) {
			if (!User::insert($this->pdo, $orion_login)) {
				return null;
			} else {
				$user = UserExtended::fetch($this->pdo, $orion_login);
				$user->loginStatus = self::LOGIN_NEW;
			}
		} else {
			$user->loginStatus = self::LOGIN_SUCCESS;
		}
		$_SESSION['user'] = $orion_login;
		return $user;
	}

	public function logout() {
		unset($_SESSION['user']);
		return true;
	}

}
