<?php
namespace controllers;


class RezervaceController extends Controller{

	public function startUp(){
		parent::startUp();
	}
	
	protected function buildSubmenu() {
		$menu = [
			["urlParams" => ["controller" => "rezervace", "action"=>"vypis"],
				"label" => "Vypis"
			],
			["urlParams" => ["controller" => "rezervace", "action"=>"zadat"],
				"label" => "Zadat"
			],
		];
		return $menu;
	}
	
	public function getDefaultAction() { return "vypis"; }
	
	public function renderVypis(){
		$this->template["pageTitle"] = "Výpis rezervací";
		$reservations = $this->pdoWrapper->getReservationsAndAll();
		
		$this->template["rezervace"] = $reservations;
	}
	
	public function renderZadat(){
		$this->template['pageTitle'] = "Zadat novou rezervaci";
		$this->template['games'] = $this->pdoWrapper->getGameTypes();
		$this->template['desks'] = $this->pdoWrapper->getDesks();
	}
	
	
}
