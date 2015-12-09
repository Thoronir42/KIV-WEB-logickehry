<?php
namespace controllers;

/**
 * Description of HomeControler
 *
 * @author Stepan
 */
class ErrorController extends Controller{
    
	const NO_CONTROLLER_FOUND = 1;
	const NOT_RECOGNISED_ACTION = 2;
	const NO_TEMPLATE = 3;
	const NO_RENDER_OR_REDIRECT = 4;
	
	public function __construct(){
		parent::__construct();
		
	}
	
	public function startUp(){
		parent::startUp();
		$this->layout = 'layout.twig';
	}
	
	public function renderError($errType, $c, $a){
		switch($errType){
			default:
				$this->template['nadpis'] = "Chyba";
				$this->template['zprava'] = "V aplikaci nastala chyba, která nebyla rozpoznána.";
			case self::NO_CONTROLLER_FOUND:
				$this->template['nadpis'] = "Nebyl nalezen kontroler";
				$this->template['zprava'] = "V systému není žádný kontroler jména <b>$c</b>";
				break;
			case self::NOT_RECOGNISED_ACTION:
				$this->template['nadpis'] = "Nebyla rozpoznána akce";
				$this->template['zprava'] = "Kontroler <b>$c</b> byl nalezen ale neobsahuje akci <b>$a</b>";
				break;
			case self::NO_TEMPLATE:
				$this->template['nadpis'] = "Nebyla nalezena šablona";
				$this->template['zprava'] = "Kontroler <b>$c</b> obsahuje akci <b>$a</b> ale pro danou akci neexistuje šablona.";
				break;
			case self::NO_RENDER_OR_REDIRECT:
				$this->template['nadpis'] = "Akce nemohla být zobrazena";
				$this->template['zprava'] = "Byla provedena akce $a kontroleru $c ale nebyla nalezena metoda pro její zobrazení, respektive nebylo provedeno přesměrování.";
				break;
		break;
		}
	}
	
}
