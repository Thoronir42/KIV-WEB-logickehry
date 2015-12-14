<?php
namespace model\database\tables;

/**
 * Description of GameBox
 *
 * @author Stepan
 */
class GameBox extends \model\database\DB_Entity{	
	
	var $game_box_id;
	
	var $game_type_id;
	
	var $tracking_code;
	
	var $add_note;
	
	var $retired;
	
}
