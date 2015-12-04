<?php
namespace controllers;


class SpravaController extends Controller{
	
	protected function buildSubmenu() {
		$menu = [
			["urlParams" => ["controller" => "sprava", "action"=>"hry"],
				"label" => "Hry"
			],
			["urlParams" => ["controller" => "sprava", "action"=>"rezervace"],
				"label" => "Rezervace"
			],
			["urlParams" => ["controller" => "sprava", "action"=>"uzivatele"],
				"label" => "Uživatelé"
			],
		];
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
		$this->template['games'] = $games;
	}
	
	public function renderUzivatele(){
		$this->template['pageTitle'] = "Správa registrovaných uživatelů";
		$this->template['users'] = $this->pdoWrapper->getUsers();
	}
	
	public function renderInventar(){
		$this->template['pageTitle'] = "Správa evidovaných herních krabic";
		$games = $this->pdoWrapper->getGameBoxes();
		$this->template['games'] = $games;
	}
	
	public function renderPridatHru(){
		$this->template['pageTitle'] = "Zavést novou hru";
	}
	
	public function renderPridatPolozku(){
		$id = $this->getParam("game_type_id");
		$game = $this->pdoWrapper->fetchGame($id);
		if(is_null($game)){
			$this->renderNotFound("Hra s id $id nebyla nalezena");
			return;
		}
		$this->template['pageTitle'] = "Přidat exemplář hry $game->game_name";
	}
	
	private function renderNotFound($message){
		echo $message;
		die;
	}
	
}
