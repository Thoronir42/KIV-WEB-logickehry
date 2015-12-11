<?php
namespace model\database\tables;

/**
 * Description of GameType
 *
 * @author Stepan
 */
class GameType extends \model\database\DB_Entity{
	
	public static function fromPOST(){
		parent::fromPOST(self::class);
	}
	
	var $game_type_id;
	
	var $game_name;
	
	var $avg_game_time;
	
	var $min_players;
	
	var $max_players;
}
