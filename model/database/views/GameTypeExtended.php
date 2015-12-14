<?php
namespace model\database\views;

use \model\database\tables\GameType;

/**
 * Description of GamyTypeWithScore
 *
 * @author Stepan
 */
class GameTypeExtended extends GameType{
	
	var $average_score;
	
	var $rating_count;
	
	var $subscribed_users;
	
	var $box_count;
}
