<?php
namespace model\database\tables;

/**
 * Description of GameType
 *
 * @author Stepan
 */
class GameType {
	
	var $game_type_id;
	
	var $game_name;
	
	var $avg_game_time;
	
	var $min_players;
	
	var $max_players;
	
	public function __construct() {
		
	}
}
