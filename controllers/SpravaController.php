<?php
namespace controllers;

use \model\ImageManager,
	model\MailManager;

class SpravaController extends Controller{
	
	protected function buildSubmenu() {
		$menu = [];
		$menu[] = ["urlParams" => ["controller" => "sprava", "action"=>"hry"],
				"label" => "Hry"];
		$menu[] = ["urlParams" => ["controller" => "sprava", "action"=>"inventar"],
				"label" => "Inventář"];
		if($this->user->isAdministrator()){
			$menu[] = ["separator" => true];
			$menu[] = ["urlParams" => ["controller" => "sprava", "action"=>"uzivatele"],
				"label" => "Uživatelé"];
			$menu[] = ["urlParams" => ["controller" => "sprava", "action"=>"ovladaciPanel"],
					"label" => "Ovládací panel"];
			$menu[] = ["urlParams" => ["controller" => "sprava", "action"=>"hromadnyMail"],
					"label" => "Hromadný mail"];
		}
		return $menu;
	}
	
	public function getDefaultAction() { return "hry"; }
	
	public function startUp(){
		parent::startUp();
		if(!$this->user->isSupervisor()){
			$this->message("Do sekce Správa nemáte přístup", \libs\MessageBuffer::LVL_WAR);
			$this->redirectPars(Controller::DEFAULT_CONTROLLER);
		}
		$this->layout = 'layout.twig';
	}
	
	public function renderHry(){
		$this->addCss("hra.css");
		$this->addCss('input-file.css');
		$this->addJs('input-file.js');
		
		$this->template['pageTitle'] = "Správa her";
		$this->template['insert_game_form_action'] = ['controller' => 'sprava', 'action' => 'pridatHru'];
		$this->template['gpr'] = 3;
		
		
		$games = $this->pdoWrapper->getGameTypesExtended();
		foreach($games as $key => $g){
			$path = $this->urlGen->img(ImageManager::get(sprintf("game_%03d", $g->game_type_id)));
			$games[$key]->picture_path = $path;
		}
		$this->template['games'] = $games;
	}
	
	public function doPridatHru(){
		$nextId = $this->pdoWrapper->getFirstUnusedGameTypeId();
		$_POST['game_type_id'] = $nextId;
		$gameType = \model\database\tables\GameType::fromPOST();
		if($gameType->readyForInsert()){
			$this->pdoWrapper->insertGameType($gameType, $nextId);
		}
		var_dump($gameType, '<hr>');
		
		var_dump("files", $_FILES, '<hr>');
		$imageResult = ImageManager::put("picture", sprintf("game_%03d", $nextId));
		var_dump($imageResult);
		
	}
	
	public function renderUzivatele(){
		if(!$this->user->isAdministrator()){
			$this->message("Do správy uživatelů nemáte přístup.");
			$this->redirect("sprava", $this->getDefaultAction());
		}
		$this->template['pageTitle'] = "Správa registrovaných uživatelů";
		$this->template['users'] = $this->pdoWrapper->getUsers();
	}
	
	public function renderInventar(){
		$retired = $this->getParam("retired");
		$this->addCss("sprava_inventar.css");
		$this->addCss("hra.css");
		$this->addJs("sprava_inventar.js");
		$this->template['pageTitle'] = "Správa evidovaných herních krabic";
		$this->template['gpr'] = 2;
		$this->template['ipr'] = 2;
		
		
		$games = $this->pdoWrapper->getGameBoxes();
		$gamesSrt = [];
		foreach($games as $g){
			if(!isset($gamesSrt[$g->game_type_id])){
				$path = $this->urlGen->img(ImageManager::get(sprintf("game_%03d", $g->game_type_id)));
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
	
	public function renderHromadnyMail(){
		$this->template['send_url'] = ['controller' => 'sprava', 'action' => 'poslatMail'];
		$this->template['games'] = $this->pdoWrapper->getGameTypesExtended();
	}
	
	public function doPoslatMail(){
		$gid = $this->getParam('game_type_id', INPUT_POST);
		$content = $this->getParam('content', INPUT_POST);
		$users = $this->pdoWrapper->subscribedUsersByGame($gid);
		var_dump($gid, $content, $users);
		
		$result = MailManager::send($users, $content);
		if($result['result']){
			$this->message($result['message'], \libs\MessageBuffer::LVL_SUC);
		} else {
			$this->message($result['message'], \libs\MessageBuffer::LVL_DNG);
		}
		
		$this->redirectPars('sprava', $this->getDefaultAction());
	}
}
