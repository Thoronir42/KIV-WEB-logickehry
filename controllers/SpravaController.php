<?php
namespace controllers;

use \model\ImageManager;

class SpravaController extends Controller{
	
	protected function buildSubmenu() {
		$menu = [];
		$menu[]= ["urlParams" => ["controller" => "sprava", "action"=>"hry"],
				"label" => "Hry"];
		$menu[]= ["urlParams" => ["controller" => "sprava", "action"=>"inventar"],
				"label" => "Inventář"];
		$menu[]= ["urlParams" => ["controller" => "sprava", "action"=>"rezervace"],
				"label" => "Rezervace"];
		$menu[]= ["urlParams" => ["controller" => "sprava", "action"=>"uzivatele"],
				"label" => "Uživatelé"];
		return $menu;
	}
	
	public function startUp(){
		parent::startUp();
		$this->layout = 'layout.twig';
	}
	
	public function renderHry(){
		$this->addCss("hra.css");
		
		$this->template['pageTitle'] = "Správa her";
		
		$games = $this->pdoWrapper->getGamesWithScores();
		foreach($games as $key => $g){
			$path = $this->urlGen->getImg(ImageManager::get(sprintf("game_%03d.png", $g->game_type_id)));
			$games[$key]->picture_path = $path;
		}
		$this->template['games'] = $games;
	}
	
	public function renderUzivatele(){
		$this->template['pageTitle'] = "Správa registrovaných uživatelů";
		$this->template['users'] = $this->pdoWrapper->getUsers();
	}
	
	public function renderInventar(){
		$this->template['pageTitle'] = "Správa evidovaných herních krabic";
		$this->addCss("sprava_inventar.css");
		$games = $this->pdoWrapper->getGameBoxes();
		$gamesSrt = [];
		foreach($games as $g){
			if(!isset($gamesSrt[$g->game_type_id])){
				$path = $this->urlGen->getImg(ImageManager::get(sprintf("game_%03d.png", $g->game_type_id)));
				$gamesSrt[$g->game_type_id] = ["game_name"=>$g->game_name,"picture_path" => $path, "tracking_codes" => []];
			}
			$gamesSrt[$g->game_type_id]["tracking_codes"][] = $g->tracking_code;
		}
		$this->template['games'] = $gamesSrt;
	}
	
	public function renderVlozitHru(){
		$id = $this->getParam("game_type_id");
		if($id != null){
		$game = $this->pdoWrapper->fetchGame($id);
		if(is_null($game)){
			$this->renderNotFound("Hra s id $id nebyla nalezena");
			return;
		}
		} else {
			$game = \model\database\tables\GameType::fromPOST();
	}
	
		$this->template['pageTitle'] = "Zavést novou hru";
	}
}
