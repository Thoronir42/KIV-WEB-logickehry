<?php
namespace controllers;

use model\GameTypeManager;

/**
 * Description of HomeControler
 *
 * @author Stepan
 */
class VypisController extends Controller{
	
	public static function getDefaultAction() { return "hry"; }
	
	
	protected function buildSubmenu(){
		return false;
		$menu = [];
		$menu[] = ["urlParams" => ["controller" => "vypis", "action"=>"hry"],
				"label" => "Seznam her"];
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
		$this->template['gpr'] = 3; // games per row
		$games = GameTypeManager::fetchAll($this->pdoWrapper);
		$this->user->setSubscribedItems($this->pdoWrapper->usersSubscribedGames($this->user->user_id));
		foreach($games as $key => $g){
			$games[$key]->detail_link = ['controller' => 'vypis', 'action' => 'detailHry', 'id' => $g->game_type_id];
		}
        $this->template['hry'] = $games;
    }
	
	public function renderDetailHry(){
		$id = $this->getParam("id");
		$gameType = GameTypeManager::fetchById($this->pdoWrapper, $id);
		if(!$gameType){
			$this->message("Požadovaná hra nebyla nalezena.", \libs\MessageBuffer::LVL_WAR);
			$this->redirectPars('vypis', 'hry');
		}
		$this->addCss("vypis_detailHry.css");
		
		$this->template['g'] = $gameType;
		$this->template['ratings'] = $this->pdoWrapper->gameRatingsByGameType($id);
		$this->template['rating'] = ['min' => 1, 'def' => 3, 'max' => 5];
	}
	
}
