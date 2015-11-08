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
		$this->layout = 'layout.twig';
		
		$this->template['title'] = "CLH";
		
	}
	
    public function renderDefault(){
        $this->template['hry'] = $this->pdoWrapper->getGamesWithScores();
    }
    
	public function renderRezervace(){
		$this->addCss("vypis_rezervace.css");
		$this->template['rezervace'] = $this->pdoWrapper->getReservationsAndAll();
		var_dump($this->template['rezervace'][0]);
	}
	
}
