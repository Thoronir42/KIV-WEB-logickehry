<?php
namespace controllers;


class RezervaceController extends Controller{

	public function startUp(){
		parent::startUp();
	}
	
	protected function buildSubmenu() {
		return false;
		/*$menu = [
			["urlParams" => ["controller" => "rezervace", "action"=>"vypis"],
				"label" => "Vypis"
			],
		];
		return $menu;*/
	}
	
	public function getDefaultAction() { return "vypis"; }
	
	public function renderVypis(){
		$this->template["pageTitle"] = "Výpis rezervací";
		
		$this->template['games'] = $this->pdoWrapper->getGameTypes();
		$this->template['desks'] = $this->pdoWrapper->getDesks();
		$this->template["rezervace"] = $this->pdoWrapper->getReservationsExtended();
	}
}
