<?php

class XMLgenerator{
    /** @var array */
    var $game_type;
    /** @var array */
    var $game_box;
    /** @var array */
    var $reservation;
    
    public function fetchWeek($week){
        $this->game_type = $this->fetchGameTypes($week);
        $this->game_box = $this->fetchGameBoxes($week);
        $this->reservation = $this->fetchReservations($week);
    }
    
    private function fetchGameTypes($week){
        $return = [];
        
        return $return;
    }
	
	private function fetchGameBoxes(){
		$return = [];
		
		return $return;
	}
    
}