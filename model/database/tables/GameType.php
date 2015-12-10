<?php
namespace model\database\tables;

/**
 * Description of GameType
 *
 * @author Stepan
 */
class GameType extends \model\database\DB_Entity{
	
	var $game_type_id;
	
	var $game_name;
	
	var $avg_game_time;
	
	var $min_players;
	
	var $max_players;
	
	public function __construct() {
		
	}
}
