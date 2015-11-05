<?php
namespace controllers;

use libs\URLgen;

/**
 * Description of Controler
 *
 * @author Stepan
 */
class Controller{
	
	/** @var URLgen */
    var $urlgen;
	
	public $menu;
	
    /** @var \TemplateContainer */
    protected $template;
    
    public function __construct() {
		$this->urlgen = new URLgen();
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
		$this->menu = self::buildUrls($menu);
	}
	
	private function buildUrls($menu){
		
		foreach($menu as $key => $item){
			$menu[$key]["url"] = $this->urlgen->getUrl($item['urlParams']);
		}
		return $menu;
	}
    
	public function setActiveMenuItem($controller = null, $action = null){
		foreach($this->menu as $key => $val){
			if($val['urlParams']['controller'] == $controller){
				$this->menu[$key]['active'] = true;
			}
		}
		
	}
	
    public function showDefault(){
        
    }
    
    public function redirect($location){
        \header("Location: /$location");
	\header("Connection: close");
    }

}
