<?php
namespace model\database\tables;

/**
 * Description of GameType
 *
 * @author Stepan
 */
class GameType extends \model\database\DB_Entity{
	
	/**
	 * 
	 * @return GameType
	 */
	public static function fromPOST(){
		return parent::fromPOST(self::class);
	}
	
	var $game_type_id = false;
	
	var $game_name;
	
	var $subtitle = false;
	
	var $avg_playtime;
	
	var $min_players;
	
	var $max_players;
	
	protected function checkRequiredProperties() {
		return parent::checkRequiredProperties(self::class);
	}
}
