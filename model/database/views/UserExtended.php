<?php

namespace model\database\views;

use \model\database\tables\User;

/**
 * Description of GamyTypeWithScore
 *
 * @author Stepan
 */
class UserExtended extends User {

	var $role_label;
	var $ratings;

	public function isSubscribedTo($id) {
		if (!$this->isLoggedIn() || !isset($this->subscribedGames)) {
			return false;
		}
		$subGames = $this->subscribedGames;
		$index = array_search($id, $subGames);
		return $index !== false;
	}

	public function setSubscribedItems($items) {
		$this->subscribedGames = $items;
	}
	
	
	public function __sleep() {
		return parent::__sleep();
	}
}
