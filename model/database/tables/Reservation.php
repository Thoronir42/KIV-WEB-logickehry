<?php
namespace model\database\tables;

/**
 * Description of Reservation
 *
 * @author Stepan
 */
class Reservation extends \model\database\DB_Entity{
	
	var $reservation_id;
	
	var $game_box_id;
	
	var $reservee_user_id;
	
	var $open_reservation;
	
	var $time_from;
	
	var $time_to;
	
	var $desk_id;
	
}
