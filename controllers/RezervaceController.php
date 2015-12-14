<?php
namespace controllers;

use model\DatetimeManager;


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
		
		$timePars = DatetimeManager::getWeeksBounds(0, DatetimeManager::DB_FORMAT);
		$reservations = $this->pdoWrapper->getReservationsExtended($timePars);
		$reservationDays = [];
		foreach($reservations as $r){
			$day = date("w", strtotime($reservations[0]->time_from));
			if(!isset($reservationDays[$day])){ $reservationDays[$day] = []; }
			$reservationDays[$day][] = $r;
		}
		$this->template["reservationDays"] = $reservationDays;
	}
}
