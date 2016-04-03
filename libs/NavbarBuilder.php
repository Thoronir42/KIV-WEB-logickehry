<?php

namespace libs;

use model\database\views\UserExtended;

class NavbarBuilder {
	
	/**
	 * 
	 * @param UserExtended $user
	 * @param type $controller
	 * @param type $action
	 * @return type
	 */
	public static function navMenu($user, $controller = null, $action = null){
		$menu = [];
		$menu['rezervace'] = self::createNavMenuItem('rezervace', 'vypis', 'Rezervace');
		$menu['vypis'] = self::createNavMenuItem('vypis', 'hry', 'Seznam her');
		
		if ($user->isSupervisor()) {
			$menu['sprava'] = self::createNavMenuItem('sprava', 'hry', 'Správa');
			$menu['sprava']['dropdown'] = self::buildSpravaSubmenu($user);
		}
		
		if( isset($menu[$controller]) ) {
			$menu[$controller]['active'] = true;
			$activeMenu = $menu[$controller];
			if( isset($activeMenu['dropdown']) && isset($activeMenu['dropdown'][$action])){
				$menu[$controller]['dropdown'][$action]['active'] = true;
			}
		}
		
		return $menu;
	}
	
	private static function createNavMenuItem($controller, $action, $label, $other = []){
		$menuItem = [
			"urlParams" => ["controller" => $controller, "action" => $action],
			"label" => $label
			];
		foreach($other as $k => $v){
			$menuItem[$k] = $v;
		}
		return $menuItem;
	}
	
	/**
	 * 
	 * @param UserExtended $user
	 * @return array
	 */
	private static function buildSpravaSubmenu($user) {
		$menu = [];
		$menu['hry'] = self::createNavMenuItem('sprava', 'hry', 'Hry');
		$menu['inventar'] = self::createNavMenuItem('sprava', 'inventar', 'Inventář');
		$menu['stoly'] = self::createNavMenuItem('sprava', 'stoly', 'Stoly');
		
		if ($user->isAdministrator()) {
			$menu[] = ["separator" => true];
			
			$menu['uzivatele'] = self::createNavMenuItem('sprava', 'uzivatele', 'Uživatelé');
			$menu['ovladaciPanel'] = self::createNavMenuItem('sprava', 'ovladaciPanel', 'Ovládací panel');
			$menu['hromadnyMail'] = self::createNavMenuItem('sprava', 'hromadnyMail', 'Hromadný mail');
		}
		return $menu;
	}
	
	/**
	 * 
	 * @param UserExtended $user
	 * @return boolean
	 */
	public static function userActions($user) {

		
		$changeDetails = self::createNavMenuItem('uzivatel', 'mujProfil', 'Můj profil');
		if (!$user->hasNickname()) {
			$changeDetails['label_class'] = 'label-info';
		}
		$separator = ["separator" => true];
		$logout = self::createNavMenuItem('uzivatel', 'odhlasitSe', 'Odhlásit se');
		
		$menu = [$changeDetails, $separator, $logout];
		return $menu;
	}
}
