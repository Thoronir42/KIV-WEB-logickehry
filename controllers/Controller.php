<?php

namespace controllers;

use libs\URLgen,
	libs\MessageBuffer;
use model\database\views\UserExtended;
use model\UserManager;

/**
 * Description of Controler
 *
 * @author Stepan
 */
abstract class Controller {

	const DEFAULT_CONTROLLER = 'rezervace';

	public static function getDefaultAction() {
		return null;
	}

	/** @var URLgen */
	var $urlGen;

	/** @var \PDO */
	var $pdo;

	/** @var MessageBuffer */
	var $mb;

	/** @var array */
	var $template;

	/** @var string */
	var $layout;

	/** @var UserExtended */
	var $user;

	/** @var array */
	var $navbar;

	/** @var String */
	var $action, $controller;

	public function __construct($support) {
		if ($support instanceof UserExtended) {
			$this->user = $support;
			return;
		}
		$this->pdo = $support['pdo'];
		$this->urlGen = $support['urlgen'];
		$this->mb = $support['mb'];

		if (isset($support['url'])) {
			$this->controller = $support['url']['controller'];
			$this->action = $support['url']['action'];
		}

		$this->user = UserManager::getCurrentUser($this->pdo);
		$this->navbar = [];
		$this->navbar['app-name'] = \config\Config::APP_NAME;
		if ($this->user->isLoggedIn()) {
			$this->navbar['user_actions'] = UzivatelController::buildUserActionsMenu($this->user);
		} else {
			$this->navbar['login_url'] = ['controller' => 'uzivatel', 'action' => 'PrihlasitSe'];
		}
		$this->navbar['session_time'] = date(\model\DatetimeManager::HUMAN_FULL, $_SESSION['LAST_ACTIVITY']);
		$this->layout = "layout.twig";
		$this->template = [
			'css' => ['bootstrap.css'],
			'js' => ['jquery-2.1.4.min.js', 'bootstrap.js'],
			'title' => \config\Config::APP_NAME,
			'user' => $this->user,
			'badgeTpl' => \config\Config::BADGE,
		];
	}

	private function buildMenu() {
		$menu = [];
		$menu[] = ["urlParams" => ["controller" => "rezervace", "action" => "vypis"],
			"label" => "Rezervace"];
		$menu[] = ["urlParams" => ["controller" => "vypis", "action" => "hry"],
			"label" => "Seznam her"];

		if ($this->user->isSupervisor()) {
			$menu[] = ["urlParams" => ["controller" => "sprava", "action" => "hry"],
				"label" => "Správa"];
		}

		foreach ($menu as $k => $v) {
			$cont = \Dispatcher::getControler($v['urlParams']['controller'], $this->user);
			if ($cont == null) {
				continue;
			}
			$menu[$k]["dropdown"] = $cont->buildSubmenu();

			unset($cont);
		}

		return $menu;
	}

	public function colSizeFromGet() {
		$size = $this->getParam('colSize');
		if (is_numeric($size) && !is_double($size) && $size >= 0 && $size <= 12) {
			return $size;
		}
		return $this->getDefaultColSize();
	}

	protected function getDefaultColSize() {
		return 6;
	}

	protected function buildSubmenu() {
		return false;
	}

	public function startUp() {
		$menu = $this->buildMenu();
		$this->navbar['menu'] = $this->activeMenuParse($menu, 'controller', $this->controller, true);
		$this->template['navbar'] = $this->navbar;
		$this->addCss("default.css");
	}

	private function activeMenuParse($menu, $checkKey, $checkVal, $continue = false) {
		if (!$menu) {
			return $menu;
		}
		foreach ($menu as $key => $val) {
			if (!isset($val['urlParams'])) {
				continue;
			}
			if ($val['urlParams'][$checkKey] == $checkVal) {
				$menu[$key]['active'] = true;
				$activeKey = $key;
				//echo "Found active $checkKey : $val[label]<br>";
				break;
			}
		}
		if (isset($activeKey) && $continue) {
			$menu[$activeKey]['dropdown'] = $this->activeMenuParse($menu[$activeKey]['dropdown'], "action", $this->action);
		}
		return $menu;
	}

	protected function getParam($name, $method = INPUT_GET) {
		switch ($method) {
			default: return null;
			case INPUT_GET: case INPUT_POST:
				$field = filter_input($method, $name);
				return $field;
		}
	}

	public function addCss($css) {
		$this->template['css'][] = $css;
	}

	public function addJs($js) {
		foreach ($this->template['js'] as $scr) {
			if ($scr === $js) {
				return;
			}
		}
		$this->template['js'][] = $js;
	}

	public function message($text, $level = MessageBuffer::LVL_INF, $link = null) {
		$this->mb->log($text, $level, $link);
	}

	public function redirect($location) {
		\header("Location: $location");
		\header("Connection: close");
		die;
	}

	public function redirectPars($controller = null, $action = null, $additional = null) {
		$url = ['controller' => $controller ? : self::DEFAULT_CONTROLLER, 'action' => $action];
		if (!empty($additional) && is_array($additional)) {
			foreach ($additional as $k => $v) {
				$url[$k] = $v;
			}
		}
		$location = $this->urlGen->url($url);
		$this->redirect($location);
	}

	public function __toString() {
		return explode("\\", get_class($this))[1];
	}

	public function preRender() {
		$this->template['alert_messages'] = $this->mb->getLog();
	}

}
