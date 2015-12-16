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
	
	var $game_type_id;
	
	var $game_name;
	
	var $subtitle;
	
	var $avg_playtime;
	
	var $min_players;
	
	var $max_players;
	
	public function readyForInsert() {
		if(parent::readyForInsert()){ return true; }
		return (sizeof($this->missing) < 2 && isset($this->missing['game_type_id']));
	}
	
}
