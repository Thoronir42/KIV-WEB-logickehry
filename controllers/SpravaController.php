<?php
namespace controllers;

use \model\GameBoxManager,
	\model\GameTypeManager,
	\model\ImageManager,
	\model\MailManager;

use model\database\tables\GameType;

class SpravaController extends Controller{
	
	public static function getDefaultAction() { return "hry"; }
	
	
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
		
		
		$games = \model\GameTypeManager::fetchAll($this->pdoWrapper);
		foreach($games as $key => $g){
			$path = $this->urlGen->img(ImageManager::get(sprintf("game_%03d", $g->game_type_id)));
			$games[$key]->picture_path = $path;
		}
		$this->template['games'] = $games;
	}
	
	public function doPridatHru(){
		$nextId = GameTypeManager::nextId($this->pdoWrapper);
		$_POST['game_type_id'] = $nextId;
		$gameType = GameType::fromPOST();
		if($gameType->readyForInsert()){
			$pars = $gameType->asArray();
			$pars['game_type_id'] = $nextId;
			if(!GameTypeManager::insert($this->pdoWrapper, $pars)){
				$this->message("Nebylo možné přidat hru na úrovni databáze", \libs\MessageBuffer::LVL_WAR);
				$this->redirectPars('sprava', 'hry');
			} else {
				$this->message("Hra $gameType->game_name byla úspěšně přidána do databáze", \libs\MessageBuffer::LVL_SUC);
				$this->redirectPars('sprava', 'hry');
			}
		} else {
			$this->message("Nebylo možné přidat hru, nebyla vyplněna následující pole: ".$gameType->getMissingParameters());
			$this->redirectPars();
		}
		$imgRes = ImageManager::put("picture", sprintf("game_%03d", $nextId));
		if($imgRes['result']){
			$this->message($imgRes['message'], \libs\MessageBuffer::LVL_SUC);
		} else {
			$this->message($imgRes['message'], \libs\MessageBuffer::LVL_WAR);
		}
		$this->redirect('sprava', 'hry');
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
		
		
		$games = GameBoxManager::fetchAll($this->pdoWrapper);
		$gamesSrt = [];
		foreach($games as $g){
			if(!isset($gamesSrt[$g->game_type_id])){
				$gamesSrt[$g->game_type_id] = $g->asArray();
				$gamesSrt[$g->game_type_id]['tracking_codes'] = [];
			}
			if($g->tracking_code && (!$g->retired || $retired)){
				$gamesSrt[$g->game_type_id]['tracking_codes'][] = $g;
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
