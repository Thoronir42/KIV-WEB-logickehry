<?php
namespace controllers;

/**
 * Description of HomeControler
 *
 * @author Stepan
 */
class VypisController extends Controller{
	
	protected function buildSubmenu(){
		$menu = [];
		$menu[] = ["urlParams" => ["controller" => "vypis", "action"=>"hry"],
				"label" => "Hry"];
		$menu[] = ["urlParams" => ["controller" => "vypis", "action"=>"krabice"],
				"label" => "Herní krabice"];
		$menu[] = ["urlParams" => ["controller" => "vypis", "action"=>"rezervace"],
				"label" => "Uživatelé"];
		$menu[] = ["urlParams" => ["controller" => "vypis", "action"=>"inventory"],
				"label" => "(XML)"];
		return $menu;
	}
	
	public function startUp(){
		parent::startUp();
		$this->layout = 'layout.twig';
		$this->template['title'] = "CLH";
		
	}
	
    public function renderHry(){
		$this->addCss("hra.css");
		$this->template['pageTitle'] = "Výpis her";
        $this->template['hry'] = $this->pdoWrapper->getGamesWithScores();
    }
	
	public function renderRezervace(){
		$this->addCss("vypis_rezervace.css");
		$this->template['pageTitle'] = "Výpis rezervací";
		$this->template['rezervace'] = $this->pdoWrapper->getReservationsAndAll();
		foreach ($this->template['rezervace'][0] as $key => $var){
			echo "$key => $var<br/>";
		}
	}
	
}
