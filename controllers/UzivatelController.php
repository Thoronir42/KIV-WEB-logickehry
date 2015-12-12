<?php
namespace controllers;

/**
 * Description of UzivatelControler
 *
 * @author Stepan
 */
class UzivatelController extends Controller{
    
	var $block_sauce = true;
	
	public function __construct(){
		parent::__construct();
	}
	
	public function renderZmenaUdaju(){
		
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
		$login = $_SESSION["orion_login"];
		unset($_SESSION['orion_login']);
		
		echo $login;
	}
}
