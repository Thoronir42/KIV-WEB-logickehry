<?php
namespace libs;

use PDO;
use model\database\views as Views;
use model\database\tables as Tables;


class PDOwrapper{
    /** @var PDO */
    var $con;
    
	/**
	 * 
	 * @param array $cfg
	 * @return \PDOwrapper
	 */
    public static function getConnection($cfg){
        $cfg['password'] = isset($cfg['password']) ? $cfg['password'] : null;
        $pdo = new PDO("mysql:host=$cfg[host];dbname=$cfg[db_name];charset=utf8", $cfg['user'], $cfg['password']);
		return new PDOwrapper($pdo);
    }
	
	/**
	 * 
	 * @param PDO $pdo
	 */
	private function __construct($pdo) {
		$this->con = $pdo;
	}
	
	public function gameRatingsByGameType($id) {
		$statement = $this->con->prepare("SELECT * FROM `game_rating_extended` "
				. "WHERE game_type_id = :id "
				. "ORDER BY time_from ASC");
		if($statement->execute(['id' => $id])){
			return $statement->fetchAll(PDO::FETCH_CLASS, Views\ReservationExtended::class);
		}
		return null;
	}
	
	public function getDesks(){
		$result = $this->con->query("SELECT * FROM desk")
				->fetchAll(PDO::FETCH_CLASS, Tables\Desk::class);
		return $result;
	}
}