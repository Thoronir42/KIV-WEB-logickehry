<?php
namespace controllers;

use model\database\views\UserExtended;

/**
 * Description of UzivatelControler
 *
 * @author Stepan
 */
class UzivatelController extends Controller{
	
	/**
	 * 
	 * @return UserExtended
	 */
	public static function getCurrentUser(){
		if(!isset($_SESSION['user'])){ return new UserExtended(); }
		$userSer = $_SESSION['user'];
		$user = unserialize($userSer);
		return $user;
	}
	
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
		if(!$this->user->isLoggedIn()){
			$this->redirectPars(Controller::DEFAULT_CONTROLLER);
		}
	}
	
	public function getDefaultAction() { return "mojeUdaje"; }
	
	public function renderMojeUdaje(){
		$this->template['form_action'] = ["controller" => "uzivatel", "action" => "ulozitUdaje"];
		$this->template['resLink'] = ['controller' => 'rezervace', 'action' => 'vypis'];
	}
	
	public function doUlozitUdaje(){
		$pars = ["orion_login"	=> $this->user->orion_login,
					"name"		=> $this->getParam("name", INPUT_POST),
					"surname"	=> $this->getParam("surname", INPUT_POST)
			];
		if($this->pdoWrapper->updateUser($pars)){
			$this->message("Vaše údaje byly zpracovány...", \libs\MessageBuffer::LVL_SUC);
			$user = unserialize($_SESSION['user']);
			$user->name		= $pars['name'];
			$user->surname	= $pars['surname'];
			$_SESSION['user'] = serialize($user);
		} else {
			$this->message("Při ukládání vašich údajů nastala chyba.", \libs\MessageBuffer::LVL_DNG);
		}
		
		$this->redirectPars("uzivatel");
	}
	
	public function doOdhlasitSe(){
		unset($_SESSION['user']);
		$this->message("Vaše odhlášení z aplikace proběhlo úspěšně.", \libs\MessageBuffer::LVL_INF);
		$this->message("Pro přihlášení pod jiným účtem se nejdříve odhlašte z orion loginu, např. na Portalu", \libs\MessageBuffer::LVL_WAR);
		$this->redirectPars();
	}
	
	public function doPrihlasitSe(){
		$loginUrl = $this->urlGen->loginUrl();
		$_SESSION['login_return_url'] = $this->urlGen->url(['controller' => 'uzivatel', 'action' => 'prihlaseni']);
		
		$this->redirect($loginUrl);
	}
	
	public function doPrihlaseni(){
		if(!isset($_SESSION['orion_login'])){
			$this->message("Neplatny pokus o prihlaseni!", \libs\MessageBuffer::LVL_WAR);
			$this->redirectPars("vypis", "hry");
		}
		$orion_login = $_SESSION["orion_login"];
		unset($_SESSION['orion_login']);
		
		$user = $this->pdoWrapper->fetchUser($orion_login);
		if(!$user){
			if(!$this->pdoWrapper->insertUser($orion_login)){
				$this->message("Nepodařilo se pro vás vytvořit uživatelský účet", \libs\MessageBuffer::LVL_DNG);
				$this->redirectPars('vypis');
			} else {
				$user = $this->pdoWrapper->fetchUser($orion_login);
				$this->message("Váš uživatelský účet byl úspěšně vytvořen, vítejte $orion_login", \libs\MessageBuffer::LVL_SUC);
			}
		} else {
			$this->message("Vítejte zpět, $orion_login!", \libs\MessageBuffer::LVL_SUC);
		}
		$_SESSION['user'] = serialize($user);
		$this->redirectPars('vypis', 'hry');
		
	}
}
