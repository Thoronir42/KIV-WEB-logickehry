<?php
namespace controllers;

use libs\URLgen,
	libs\PDOwrapper;

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
	
    public function __construct() {
		$this->template = [
			'css' => [],
			'script' => [],
		];
		$menu = [
			["urlParams" => ["controller" => "vypis", "action"=>"vse"],
				"label" => "Výpis týdne"
			],
			["urlParams" => ["controller" => "sprava", "action"=>"hry"],
				"label" => "Správa her"
			],
			["urlParams" => ["controller" => "letiste", "action"=>"rezervace"],
				"label" => "(Letiště)"
			],
			["urlParams" => ["controller" => "xml", "action"=>"inventory"],
				"label" => "(XML)"
			],
		];
		$this->template['menu'] = $menu;
	}
	
	public function startUp(){
		$this->template['menu'] = $this->buildUrls($this->template['menu']);
		$this->addCss("default.css");
	}
	
	private function buildUrls($menu){
		
		foreach($menu as $key => $item){
			$menu[$key]["url"] = $this->urlGen->getUrl($item['urlParams']);
		}
		return $menu;
	}
    
	public function setActiveMenuItem($controller = null, $action = null){
		$menu = $this->template['menu'];
		foreach($menu as $key => $val){
			if($val['urlParams']['controller'] == $controller){
				$menu[$key]['active'] = true;
			}
		}
		$this->template['menu'] = $menu;
	}
	
    public function renderDefault(){
        
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
