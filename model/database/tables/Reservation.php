<?php
namespace model\database\tables;

/**
 * Description of Reservation
 *
 * @author Stepan
 */
class Reservation extends \model\database\DB_Entity{
	
	public static function fromPOST(){ return parent::fromPOST(self::class); }
	
	
	var $reservation_id;
	
	var $game_box_id;
	
	var $reservee_user_id;
	
	var $open_reservation = false;
	
	var $time_from;
	
	var $time_to;
	
	var $desk_id = false;
	
}
