<?php
namespace model\database\tables;

/**
 * Description of Reservation
 *
 * @author Stepan
 */
class Reservation extends \model\database\DB_Entity{
	
	const EARLY_RESERVATION = 7;
	const LATE_RESERVATION = 19;
	
	const WEEK_START_DAY = 1;
	const WEEK_END_DAY = 7;
	
	const RES_TYPE_OPEN = ['type' => 1, 'label' => 'Rezervace otevřená pro přihlášení ostatním členů'];
	const RES_TYPE_CLOSED = ['type' => 2, 'label' => 'Pouze rezervace'];
	const RES_TYPE_EVENT = ['type' => 3, 'label' => 'Celodenní událost'];
	
	public static function getTypes($includeEvent = false){
		$return = [
			self::RES_TYPE_OPEN,
			self::RES_TYPE_CLOSED,
		];
		if($includeEvent){
			$return[] = self::RES_TYPE_EVENT;
		}
		return $return;
	}
	/**
	 * 
	 * @param \PDO $pdo
	 * @param mixed[] $pars
	 */
	public static function insert($pdo, $pars){
		$pars['time_to'] = date(\model\DatetimeManager::DB_FORMAT, $pars['time_to']);
		$pars['time_to'] = date(\model\DatetimeManager::DB_FORMAT, $pars['time_to']);
		$statement = $pdo->prepare("INSERT INTO `web_logickehry_db`.`reservation` "
		. "(`reservation_id`, `game_box_id`, `reservee_user_id`, `open_reservation`, `time_from`, `time_to`, `desk_id`)
		VALUES (NULL,		  :game_box_id , :reservee_user_id, '1', '2015-12-14 12:23:00', '2015-12-14 14:00:00', '1'");
		if ($statement->execute($pars)){
			return true;
		}
		var_dump($statement->errorInfo());
		return false;
	}
	
	public static function fromPOST(){ return parent::fromPOST(self::class); }
	
	
	var $reservation_id;
	
	var $game_box_id;
	
	var $reservee_user_id;
	
	var $open_reservation = false;
	
	var $time_from;
	
	var $time_to;
	
	var $desk_id = false;
	
}
