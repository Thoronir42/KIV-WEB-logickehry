<?php
namespace controllers;

use \libs\PDOwrapper;


class XMLgenerator{
	
	/** @var boolean */
	var $blockSauce = true;
	
	/** @var PDOwrapper */
	var $pdoWrapper;
	
	/** @var string */
	var $layout;
	
	/** @var array */
	var $game_type;
	/** @var array */
	var $game_box;
	/** @var array */
	var $reservation;
	
	/** @var array */
	var $template;
	
	public function buildSubmenu(){ return false; }
	
	public function __construct() {
		$this->template = [];
		$this->layout = "xml.twig";
	}
	
	public function startUp(){
		header('Content-type: text/xml');
		header('Pragma: public');
		header('Cache-control: private');
		header('Expires: -1');
	}
	
	public function renderInventory(){
		$this->template["game_type"] = \model\GameTypeManager::fetchAll($this->pdoWrapper);
		$this->template["game_box"] = \model\GameBoxManager::fetchAll($this->pdoWrapper, false);
	}
	
	public function renderReservations(){
		
	}
	
	
    public function fetchWeek($week){
        $this->game_type = $this->fetchGameTypes($week);
        $this->game_box = $this->fetchGameBoxes($week);
        $this->reservation = $this->fetchReservations($week);
    }
	
	public function preRender(){ return false; }
	
	
	public function __toString() {
		return explode("\\", get_class($this))[1];
	}
    
}