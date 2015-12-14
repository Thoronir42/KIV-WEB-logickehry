<?php
namespace controllers;

use \model\ImageManager;

class SpravaController extends Controller{
	
	protected function buildSubmenu() {
		$menu = [];
		$menu[] = ["urlParams" => ["controller" => "sprava", "action"=>"hry"],
				"label" => "Hry"];
		$menu[] = ["urlParams" => ["controller" => "sprava", "action"=>"inventar"],
				"label" => "Inventář"];
		$menu[] = ["urlParams" => ["controller" => "sprava", "action"=>"rezervace"],
				"label" => "Rezervace"];
		$menu[] = ["urlParams" => ["controller" => "sprava", "action"=>"uzivatele"],
				"label" => "Uživatelé"];
		if($this->user->isAdministrator()){
			$menu[] = ["separator" => true];
			$menu[] = ["urlParams" => ["controller" => "sprava", "action"=>"ovladaciPanel"],
					"label" => "Ovládací panel"];
		}
		return $menu;
	}
	
	public function getDefaultAction() { return "hry"; }
	
	public function startUp(){
		parent::startUp();
		if(!$this->user->isSupervisor()){
			$this->message("Do sekce Správa nemáte přístup", \libs\MessageBuffer::LVL_WAR);
			$this->redirectPars('vypis');
		}
		$this->layout = 'layout.twig';
	}
	
	public function renderHry(){
		$this->addCss("hra.css");
		
		$this->template['pageTitle'] = "Správa her";
		
		$games = $this->pdoWrapper->getGamesWithScores();
		foreach($games as $key => $g){
			$path = $this->urlGen->img(ImageManager::get(sprintf("game_%03d.png", $g->game_type_id)));
			$games[$key]->picture_path = $path;
		}
		$this->template['games'] = $games;
	}
	
	public function renderUzivatele(){
		$this->template['pageTitle'] = "Správa registrovaných uživatelů";
		$this->template['users'] = $this->pdoWrapper->getUsers();
	}
	
	public function renderInventar(){
		$retired = $this->getParam("retired");
		$this->addCss("sprava_inventar.css");
		$this->addJs("sprava_inventar.js");
		$this->template['pageTitle'] = "Správa evidovaných herních krabic";
		
		$games = $this->pdoWrapper->getGameBoxes();
		$gamesSrt = [];
		foreach($games as $g){
			if(!isset($gamesSrt[$g->game_type_id])){
				$path = $this->urlGen->img(ImageManager::get(sprintf("game_%03d.png", $g->game_type_id)));
				$gamesSrt[$g->game_type_id] = ["game_name"=>$g->game_name, 
					"game_type_id"=>$g->game_type_id, "picture_path" => $path,
					"tracking_codes" => []];
			}
			if($g->tracking_code && (!$g->retired || $retired)){
				$gamesSrt[$g->game_type_id]["tracking_codes"][] = $g;
			}
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
	
	public function renderOvladaciPanel(){
		$this->template['xml_inventory'] = ['controller' => 'xml', 'action' => 'inventory'];
		$this->template['xml_reservations'] = ['controller' => 'xml', 'action' => 'reservations'];
	}
}
