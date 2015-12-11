<?php
namespace controllers;

use libs\URLgen,
	libs\PDOwrapper;
use model\database\tables\User;

/**
 * Description of Controler
 *
 * @author Stepan
 */
abstract class Controller{
	
	/** @var URLgen */
    var $urlGen;
	
	/** @var PDOwrapper */
	var $pdoWrapper;
	
    /** @var array */
    var $template;
    
	/** @var string */
	var $layout;
	
	/** @var User */
	var $user;
	
	/** @var array */
	var $navbar;
	
	/** @var String */
	var $action, $controller;
	
	
	
	
    public function __construct() {
		$this->navbar = ['app-name' => "Centrum Logických Her"];
		
		
		$this->layout = "layout.twig";
		$this->template = [
			'css' => [],
			'js' => [],
		];
		
		$this->user = new User();
		
	}
	private function buildMenu(){
		$menu = [];
		$menu[] = ["urlParams" => ["controller" => "vypis", "action"=>"hry"],
				"label" => "Seznamy"];
		$menu[] = ["urlParams" => ["controller" => "rezervace", "action"=>"vypis"],
				"label" => "Rezervace"];
		if($this->user->isSupervisor()){
			$menu[] = ["urlParams" => ["controller" => "sprava", "action"=>"hry"],
					"label" => "Správa"];
			$menu[] = ["urlParams" => ["controller" => "letiste", "action"=>"rezervace"],
				"label" => "(Letiště)"];
			$menu[] = ["urlParams" => ["controller" => "xml", "action"=>"inventory"],
					"label" => "(XML)"];
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


	public function startUp(){
		$menu = $this->buildUrls($this->buildMenu(), true);
		$this->navbar['menu'] = $this->activeMenuParse($menu, 'controller', $this->controller, true);
		$this->template['navbar'] = $this->navbar;
		$this->addCss("default.css");
		
	}
	
	private function buildUrls($menu, $recursion = false){
		if(!$menu){
			return false;
		}
		foreach($menu as $key => $item){
			$menu[$key]["url"] = $this->urlGen->url($item['urlParams']);
			if($recursion){
				$menu[$key]['dropdown'] = $this->buildUrls($item['dropdown']);
			}
		}
		return $menu;
	}
    
	public function setActiveMenuItem($controller = null, $action = null){
		$this->controller = $controller;
		$this->action = $action; 
	}
	private function activeMenuParse($menu, $checkKey, $checkVal, $continue = false){
		if(!$menu){ return $menu; }
		foreach($menu as $key => $val){
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
	
    public function renderDefault(){
        
    }
	
	protected function getUser(){
		
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
    
    public function redirect($location){
        \header("Location: /$location");
		\header("Connection: close");
    }
	
	public function __toString() {
		return explode("\\", get_class($this))[1];
	}
	
}
