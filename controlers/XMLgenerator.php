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
        
    }
    
    private function fetchGameTypes($week){
        $return = [];
        
        return $return;
    }
    
}