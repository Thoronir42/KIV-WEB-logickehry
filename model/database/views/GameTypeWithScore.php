<?php
namespace model\database\views;

use \model\database\tables\GameType;

/**
 * Description of GamyTypeWithScore
 *
 * @author Stepan
 */
class GameTypeWithScore extends GameType{
	
	var $average_score;
	
	var $rating_count;
	
	var $subsribed_users;
	
	public function __construct() {
		;
	}
}
