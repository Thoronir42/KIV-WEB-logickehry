<?php
namespace model\database\tables;

/**
 * Description of User
 *
 * @author Stepan
 */
class GameRating extends \model\database\DB_Entity{
	
	var $game_rating_id;
	
	var $game_type_id;
	
	var $score;
	
	var $review;
}
