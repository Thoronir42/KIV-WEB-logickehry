<?php
namespace controllers;

/**
 * Description of HomeControler
 *
 * @author Stepan
 */
class VypisController extends Controller{
    
	
	public function startUp(){
		parent::startUp();
		$this->layout = 'layout.tpl';
		
		$this->template['title'] = "CLH";
		$this->template['css'][] = $this->urlGen->getCss("default.css");
		
		;
		
	}
	
    public function renderDefault(){
        $this->template['hry'] = $this->pdoWrapper->getGamesWithScores();
    }
    
	public function renderRezervace(){
		$this->template['rezervace'] = $this->pdoWrapper->getReservationsAndAll();
		var_dump($this->template['rezervace'][0]);
	}
	
}
