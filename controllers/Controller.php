<?php
namespace controllers;

use libs\URLgen;

/**
 * Description of Controler
 *
 * @author Stepan
 */
abstract class Controller{
	
	/** @var URLgen */
    var $urlGen;
	
    /** @var array */
    var $template;
    
    public function __construct($urlGen) {
		$this->urlGen = $urlGen;
		$this->template = [];
		
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
			["urlParams" => ["controller" => "xml", "action"=>"week"],
				"label" => "(XML)"
			],
		];
		$this->template['menu'] = $this->buildUrls($menu);
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
    
    public function redirect($location){
        \header("Location: /$location");
		\header("Connection: close");
    }

}
