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
	
    public function __construct() {
		$this->layout = "layout.twig";
		$this->template = [
			'css' => [],
			'script' => [],
			'title' => "Centrum Logických Her",
		];
		
		$this->user = new User();
		
		$this->template['menu'] = $this->buildMenu();
		$this->template['submenu'] = $this->buildSubmenu();
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
		}
		$menu[] = ["urlParams" => ["controller" => "letiste", "action"=>"rezervace"],
				"label" => "(Letiště)"];
		$menu[] = ["urlParams" => ["controller" => "xml", "action"=>"inventory"],
				"label" => "(XML)"];
		return $menu;
	}
	protected function buildSubmenu(){ return false; }


	public function startUp(){
		$this->template['menu']    = $this->buildUrls($this->template['menu']);
		$this->template['submenu'] = $this->buildUrls($this->template['submenu']);
		$this->addCss("default.css");
	}
	
	private function buildUrls($menu){
		if(!$menu){
			return false;
		}
		foreach($menu as $key => $item){
			$menu[$key]["url"] = $this->urlGen->getUrl($item['urlParams']);
		}
		return $menu;
	}
    
	public function setActiveMenuItem($controller = null, $action = null){
		$menu		= $this->template['menu'];
		foreach($menu as $key => $val){
			if($val['urlParams']['controller'] == $controller){
				$menu[$key]['active'] = true;
			}
		}
		$this->template['menu'] = $menu;
		
		$submenu	= $this->template['submenu'];
		foreach($submenu as $key => $val){
			if($val['urlParams']['action'] == $action){
				$submenu[$key]['active'] = true;
			}
		}
		
		$this->template['submenu'] = $submenu;
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
		$this->template['css'][] = $this->urlGen->getCss($css);
	}
    
    public function redirect($location){
        \header("Location: /$location");
		\header("Connection: close");
    }
	
	public function __toString() {
		return explode("\\", get_class($this))[1];
	}
	
}
