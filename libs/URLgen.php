<?php

namespace libs;

use model\ImageManager,
	config\Config;

class URLgen {

	const ADDR_SEP = '/';
	const LOGO_FILENAME = 'clh.png';

	var $urlPrefix;

	public function __construct($prefix) {
		$this->urlPrefix = $prefix;
	}

	public function getContAct() {
		$redirect = false;
		if (!Config::USE_NICE_URL) {
			$controller = filter_input(INPUT_GET, 'controller');
			$action = filter_input(INPUT_GET, 'action');
		} else {
			$url = filter_input(INPUT_GET, 'q');
			$parts = explode(self::ADDR_SEP, $url);
			if (count($parts) < 2) {
				$redirect = true;
			} else {
				$controller = $parts[0];
				$action = $parts[1];
			}
		}
		return ['controller' => $controller, 'action' => $action, 'redirect' => $redirect];
	}

	public function ajaxUrl($action = null) {
		return $this->url(['controller' => 'ajax',
					'action' => $action ? : \controllers\AjaxController::WILDCARD]);
	}

	public function url($params = null) {
		if (Config::USE_NICE_URL) {
			return $this->niceUrl($params);
		}
		$return = $this->urlPrefix;
		if (empty($params)) {
			return $return;
		}
		$first = true;
		foreach ($params as $parKey => $parVal) {
			$return.=($first ? "?" : "&") . "$parKey=$parVal";
			$first = false;
		}
		return $return;
	}

	private function niceUrl($params) {
		$return = $this->urlPrefix;
		if (empty($params)) {
			return $return;
		}
		if (isset($params['controller'])) {
			$return .= $params['controller'] . self::ADDR_SEP;
			unset($params['controller']);
		}
		if (isset($params['action'])) {
			$return .= $params['action'] . self::ADDR_SEP;
			unset($params['action']);
		}


		$first = true;
		foreach ($params as $parKey => $parVal) {
			$return.=($first ? "?" : "&") . "$parKey=$parVal";
			$first = false;
		}
		return $return;
	}

	public function loginUrl() {
		return $this->urlPrefix . "webauth/";
	}

	public function fbSub() {
		$pars = ['controller' => 'ohlas', 'action' => 'pridat'];
		return $this->url($pars);
	}

	public function urlReserve($game_type_id) {
		$params = ['controller' => 'rezervace', 'action' => 'vypis', 'game_id' => $game_type_id];
		return $this->url($params);
	}

	public function css($file) {
		return $this->urlPrefix . "css/" . $file;
	}

	public function js($file) {
		return $this->urlPrefix . "js/" . $file;
	}

	public function img($file) {
		return $this->urlPrefix . "images/" . $file;
	}

	public function gImg($game_type_id) {
		$filename = sprintf("game_%03d", $game_type_id);
		$path = ImageManager::get($filename);
		return $this->img($path);
	}

	public function appLogo() {
		return $this->img(self::LOGO_FILENAME);
	}

	public function gDet($game_type_id, $highlight = null) {
		$args = [ 'controller' => 'vypis',
			'action' => 'detailHry',
			'id' => $game_type_id];
		if ($highlight) {
			$args['highlight'] = $highlight;
		}
		return $this->url($args);
	}

	public function weDetail($type, $reservation_id) {
		switch ($type) {
			default: 
				return null;
			case \model\database\views\ReservationExtended::TYPE:
				$args = [ 'controller' => 'rezervace',
					'action' => 'detail',
					'id' => $reservation_id];
				break;
			case \model\database\tables\Event::TYPE:
				$args = [ 'controller' => 'udalost',
					'action' => 'zobrazit',
					'id' => $reservation_id];
				break;
		}
		return $this->url($args);
	}

	public function uProfile($orion_login) {
		$args = [ 'controller' => 'uzivatel',
			'action' => 'zobrazitProfil',
			'login' => $orion_login];
		return $this->url($args);
	}

}
