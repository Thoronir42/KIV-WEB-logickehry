<?php
namespace controllers;

use libs\URLgen,
	libs\PDOwrapper,
	libs\MessageBuffer;
use model\database\views\UserExtended;

/**
 * Description of Controler
 *
 * @author Stepan
 */
abstract class Controller{
	
	const DEFAULT_CONTROLLER = 'rezervace';
	
	/** @var URLgen */
    var $urlGen;
	
	/** @var PDOwrapper */
	var $pdoWrapper;
	
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
	
	/** @var MessageBuffer */
	var $messageBuffer;
	
	
	
    public function __construct() {
		$this->user = UzivatelController::getCurrentUser();
		$this->navbar = [];
		$this->navbar['app-name'] ="Centrum Logických Her";
		if($this->user->isLoggedIn()){
			$this->navbar['user_actions'] = UzivatelController::buildUserActionsMenu($this->user);
		} else {
			$this->navbar['login_url'] = ['controller' => 'uzivatel', 'action' => 'PrihlasitSe'];	
		}
		$this->navbar['session_time'] = date("d/m/y H:i:s", $_SESSION['LAST_ACTIVITY']);
		$this->layout = "layout.twig";
		$this->template = [
			'css' => [],
			'js' => [],
			'title' => $this->navbar['app-name'], 
			'user' => $this->user,
		];
		
		
	}
	private function buildMenu(){
		$menu = [];
		$menu[] = ["urlParams" => ["controller" => "rezervace", "action"=>"vypis"],
				"label" => "Rezervace"];
		$menu[] = ["urlParams" => ["controller" => "vypis", "action"=>"hry"],
				"label" => "Seznam her"];
		
		if($this->user->isSupervisor()){
			$menu[] = ["urlParams" => ["controller" => "sprava", "action"=>"hry"],
					"label" => "Správa"];
		}
		
		foreach($menu as $k => $v){
			$cont = \Dispatcher::getControler($v['urlParams']['controller']);
			if($cont == null){ continue; }
			$menu[$k]["dropdown"] = $cont->buildSubmenu();
			
			unset($cont);
		}
		
		return $menu;
	}
	protected function buildSubmenu(){ return false; }
	public function getDefaultAction(){ return null; }

	public function startUp(){
		$menu = $this->buildMenu();
		$this->navbar['menu'] = $this->activeMenuParse($menu, 'controller', $this->controller, true);
		$this->template['navbar'] = $this->navbar;
		$this->addCss("default.css");	
	}
    
	public function setActiveMenuItem($controller = null, $action = null){
		$this->controller = $controller;
		$this->action = $action; 
	}
	private function activeMenuParse($menu, $checkKey, $checkVal, $continue = false){
		if(!$menu){ return $menu; }
		foreach($menu as $key => $val){
			if(!isset($val['urlParams'])){ continue; }
			if($val['urlParams'][$checkKey] == $checkVal){
				$menu[$key]['active'] = true;
				$activeKey = $key;
				//echo "Found active $checkKey : $val[label]<br>";
				break;
			}
		}
		if(isset($activeKey) && $continue){
			$menu[$activeKey]['dropdown'] =  $this->activeMenuParse($menu[$activeKey]['dropdown'], "action", $this->action);
		}
		return $menu;
	}
	
	protected function getParam($name, $method = INPUT_GET){
		switch($method){
			default: return null;
			case INPUT_GET: case INPUT_POST:
				$field = filter_input($method, $name);
				return $field;
		}
	}
	
	protected function addCss($css){
		$this->template['css'][] = $css;
	}
	protected function addJs($js){
		foreach($this->template['js'] as $scr){
			if ($scr === $js){ return; }
		}
		$this->template['js'][] = $js;
	}
	
	protected function message($text, $level = MessageBuffer::LVL_INF, $link = null){
		$this->messageBuffer->log($text, $level, $link);
	}
	
    public function redirect($location){
        \header("Location: $location");
		\header("Connection: close");
    }
	
	public function redirectPars($controller = null, $action = null) {
		$location = $this->urlGen->url(['controller' => $controller ?: self::DEFAULT_CONTROLLER, "action" => $action]);
		$this->redirect($location);
	}
	
	public function __toString() {
		return explode("\\", get_class($this))[1];
	}
	
	
	public function preRender(){
		$this->template['alert_messages'] = $this->messageBuffer->getLog();
	}
	
}
