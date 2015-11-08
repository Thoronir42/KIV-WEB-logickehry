<?php
namespace controllers;

/**
 * Description of HomeControler
 *
 * @author Stepan
 */
class ErrorController extends Controller{
    
	public function __construct(){
		parent::__construct();
		
	}
	
	public function startUp(){
		parent::startUp();
		$this->layout = 'layout.twig';
	}
	
	public function renderNoControllerFound($c){
		$this->template['nadpis'] = "Nebyl nalezen kontroler";
		$this->template['zprava'] = "V systému není žádný kontroler jména $c";
		
	}
	
	public function renderNotRecognizedAction($c, $a){
		$this->template['nadpis'] = "Nebyla rozpoznána akce";
		$this->template['zprava'] = "Kontroler $c byl nalezen ale neobsahuje akci $a";
	}
	
	public function renderNoTemplate($c, $a){
		$this->template['nadpis'] = "Nebyla nalezena šablona";
		$this->template['zprava'] = "Kontroler $c obsahuje akci $a ale pro danou" 
			."akci neexistuje šablona.";
	}
	
	public function renderNoRenderFound($c, $a){
		$this->template['nadpis'] = "Akce nemohla být zobrazena";
		$this->template['zprava'] = "Byla provedena akce $a kontroleru $c ale "
				. "nebyla nalezena metoda pro její zobrazení, respektive nebylo"
				. "provedeno přesměrování.";
	}
	
}
