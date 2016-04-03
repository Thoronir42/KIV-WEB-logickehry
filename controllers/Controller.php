<?php

namespace controllers;

use libs\URLgen,
	libs\MessageBuffer,
	libs\DatetimeManager,
	libs\MessageBufferInsertor;
use libs\NavbarBuilder;

use model\database\views\UserExtended;
use model\Users;

use libs\ReservationManager;
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
	
	/** @var type */
	protected $reservationManager;

	public function __construct($support) {
		if ($support instanceof UserExtended) {
			$this->user = $support;
			return;
		}
		
		$this->pdo = $support['pdo'];
		$this->urlGen = $support['urlgen'];
		$this->mb = $support['mb'];
		$this->message = $this->mb->getInsertor();
		$this->reservationManager = new ReservationManager($this->pdo);

		$this->user = Users::getCurrentUser($this->pdo);
		$this->layout = "layout.twig";
		$this->template = [
			'css' => ['bootstrap.css'],
			'js' => ['jquery-2.1.4.min.js', 'bootstrap.js'],
			'title' => \config\Config::APP_NAME,
			'user' => $this->user,
			'navbar' => $this->createNavbar(),
			'badgeTpl' => \config\Config::BADGE,
		];
	}
	
	private function createNavbar(){
		$navbar = [];
		$navbar['app-name'] = \config\Config::APP_NAME;
		
		$navbar['menu'] = NavbarBuilder::navMenu($this->user, $this->urlGen->getController(), $this->urlGen->getAction());
		if ($this->user->isLoggedIn()) {
			$navbar['user_actions'] = NavbarBuilder::userActions($this->user);
		} else {
			$navbar['login_url'] = ['controller' => 'uzivatel', 'action' => 'PrihlasitSe'];
		}
		$navbar['session_time'] = date(DatetimeManager::HUMAN_FULL, $_SESSION['LAST_ACTIVITY']);
		
		return $navbar;
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

	public function startUp() {
		$this->addCss("default.css");
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
