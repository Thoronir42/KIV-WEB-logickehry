<?php
namespace model\database\views;

use \model\database\tables\Reservation;

/**
 * Description of GamyTypeWithScore
 *
 * @author Stepan
 */
class ReservationExtended extends Reservation{
	
	var $borrower_name;
	
	var $tracking_code;
	
	var $game_name;
	
	var $min_players;
	
	var $signed_players;
	
	var $max_players;
	
	var $desk_capacity;
	

}
