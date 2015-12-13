<?php
namespace controllers;

use model\ImageManager;

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
		return $menu;
	}
	
	public function getDefaultAction() { return "hry"; }
	
	public function startUp(){
		parent::startUp();
		$this->layout = 'layout.twig';
		$this->template['title'] = "CLH";
		
	}
	
    public function renderHry(){
		$this->addCss("hra.css");
		$this->template['pageTitle'] = "Výpis her";
		$games = $this->pdoWrapper->getGamesWithScores();
		foreach($games as $key => $g){
			$path = $this->urlGen->img(ImageManager::get(sprintf("game_%03d.png", $g->game_type_id)));
			$games[$key]->picture_path = $path;
		}
        $this->template['hry'] = $games;
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
