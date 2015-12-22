<?php
namespace controllers;

use model\UserManager;

/**
 * Description of UzivatelControler
 *
 * @author Stepan
 */
class UzivatelController extends Controller{
	
	const PORTAL_LOGOUT_URL = 'https://portal.zcu.cz/portal/logout';
	
	public static function getDefaultAction() { return "mojeUdaje"; }
	
	
	public static function buildUserActionsMenu($user){
		
		$changeDetails = ["urlParams" => ["controller" => "uzivatel", "action"=>"mojeUdaje"],
				"label" => "Moje údaje"];
		$changeDetails['danger'] = !$user->isReady();
		$separator = ["separator" => true];
		$logout = ["urlParams" => ["controller" => "uzivatel", "action"=>"odhlasitSe"],
				"label" => "Odhlásit se"];
		$menu = [$changeDetails, $separator, $logout];
		return $menu;	
	}
	
	public function startUp() {
		parent::startUp();
	}
	
	public function renderMojeUdaje(){
		if(!$this->user->isLoggedIn()){
			$this->redirectPars(Controller::DEFAULT_CONTROLLER);
		}
		$this->template['form_action'] = ["controller" => "uzivatel", "action" => "ulozitUdaje"];
		
		$this->template['subscriptions'] = $this->buildSubscriptions();
		$this->template['ratings'] = $this->buildRatings();
		$this->template['reservations'] = $this->buildReservations();
		
		$this->template['resLink'] = ['controller' => 'rezervace', 'action' => 'vypis'];
		$this->template['gameListLink'] = ['controller' => 'vypis', 'action' => 'hry'];
	}
	
	private function buildSubscriptions(){
		$ret = [];
		$ret['list'] = \model\SubscriptionManager::fetchGamesDetailedByUser(
				$this->pdoWrapper,
				$this->user->user_id);
		$ret['gpr'] = 2;
		return $ret;
	}
	
	private function buildRatings(){
		$ret = [];
		
		return $ret;
	}
	
	private function buildReservations(){
		$ret = [];
		
		return $ret;
	}
	
	public function doUlozitUdaje(){
		$pars = ["orion_login"	=> $this->user->orion_login,
					"name"		=> $this->getParam("name", INPUT_POST),
					"surname"	=> $this->getParam("surname", INPUT_POST)
			];
		if (UserManager::update($this->pdoWrapper, $pars)) {
			$this->message("Vaše údaje byly zpracovány...", \libs\MessageBuffer::LVL_SUC);
		} else {
			$this->message("Při ukládání vašich údajů nastala chyba.", \libs\MessageBuffer::LVL_DNG);
		}
		
		$this->redirectPars("uzivatel", $this->getDefaultAction());
	}
	
	public function doOdhlasitSe(){
		UserManager::logout();
		$this->message("Vaše odhlášení z aplikace proběhlo úspěšně.", \libs\MessageBuffer::LVL_INF);
		$this->message("Pro přihlášení pod jiným účtem se nejdříve odhlašte z orion loginu", \libs\MessageBuffer::LVL_WAR,
				['label' => "Odhlásit se", 'url' => self::PORTAL_LOGOUT_URL]);
		$this->redirectPars();
	}
	
	public function doPrihlasitSe(){
		$loginUrl = $this->urlGen->loginUrl();
		$_SESSION['login_return_url'] = $this->urlGen->url(['controller' => 'uzivatel', 'action' => 'prihlaseni']);
		$this->redirect($loginUrl);
	}
	
	public function doPrihlaseni(){
		if(!isset($_SESSION['orion_login'])){
			$this->message("Neplatny pokus o přihlášení!", \libs\MessageBuffer::LVL_WAR);
			$this->redirectPars();
		}
		$orion_login = $_SESSION["orion_login"];
		unset($_SESSION['orion_login']);
		
		$user = UserManager::login($this->pdoWrapper, $orion_login);
		if(!$user){
			$this->message("Nepodařilo se pro vás vytvořit uživatelský účet", \libs\MessageBuffer::LVL_DNG);
			$this->redirectPars();
		}
		switch($user->loginStatus){
			case UserManager::LOGIN_NEW:
				$this->message("Váš uživatelský účet byl úspěšně vytvořen, vítejte $orion_login", \libs\MessageBuffer::LVL_SUC);
				break;
			case UserManager::LOGIN_SUCCESS:
				$this->message("Vítejte zpět, $orion_login!", \libs\MessageBuffer::LVL_SUC);
				break;
		}
		$this->redirectPars();
		
	}
}
