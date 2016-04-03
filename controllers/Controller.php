<?php

namespace controllers;

use libs\URLgen,
	libs\MessageBuffer,
	libs\DatetimeManager,
	libs\MessageBufferInsertor;

use model\database\views\UserExtended;
use model\Users;
use model\services\DB_Service;

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
	
	/**	@var MessageBufferInsertor */
	var $message;

	/** @var array */
	var $template;

	/** @var string */
	var $layout;

	/** @var UserExtended */
	var $user;

	/** @var array */
	var $navbar;

	public function __construct($support) {
		if ($support instanceof UserExtended) {
			$this->user = $support;
			return;
		}
		
		$this->pdo = $support['pdo'];
		$this->urlGen = $support['urlgen'];
		$this->mb = $support['mb'];
		$this->message = $this->mb->getInsertor();

		$this->user = Users::getCurrentUser($this->pdo);
		$this->navbar = $this->createNavbar();
		$this->layout = "layout.twig";
		$this->template = [
			'css' => ['bootstrap.css'],
			'js' => ['jquery-2.1.4.min.js', 'bootstrap.js'],
			'title' => \config\Config::APP_NAME,
			'user' => $this->user,
			'badgeTpl' => \config\Config::BADGE,
		];
	}
	
	private function createNavbar(){
		$navbar = [];
		$navbar['app-name'] = \config\Config::APP_NAME;
		if ($this->user->isLoggedIn()) {
			$navbar['user_actions'] = UzivatelController::buildUserActionsMenu($this->user);
		} else {
			$navbar['login_url'] = ['controller' => 'uzivatel', 'action' => 'PrihlasitSe'];
		}
		$navbar['session_time'] = date(DatetimeManager::HUMAN_FULL, $_SESSION['LAST_ACTIVITY']);
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
		$controller = $this->urlGen->getController();
		$this->navbar['menu'] = $this->activeMenuParse($menu, 'controller', $controller, true);
		$this->template['navbar'] = $this->navbar;
		$this->addCss("default.css");
	}

	private function activeMenuParse($menu, $checkKey, $checkVal, $continue = false) {
		$currentAction = $this->urlGen->getAction();
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
			$menu[$activeKey]['dropdown'] = $this->activeMenuParse($menu[$activeKey]['dropdown'], "action", $currentAction);
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
		$messages = array_merge(DB_Service::getErrorLog(), $this->mb->getLog());
		$this->template['alert_messages'] = $messages;
	}

}
