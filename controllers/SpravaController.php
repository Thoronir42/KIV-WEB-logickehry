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
		$games = $this->pdoWrapper->getGamesWithScores();
		foreach($games as $game){
			var_dump($game);
			echo '<hr/>';
		}
		$this->template['games'] = $games;
	}
	
	public function renderPridatHru(){
		
	}
	
}
