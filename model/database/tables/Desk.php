<?php

namespace model\database\tables;

use model\database\DB_Entity;
use model\services\DB_Service;

/**
 * Description of User
 *
 * @author Stepan
 */
class Desk extends DB_Entity {

	const NO_DESK = 0;
	
	/**
	 * 
	 * @param \PDO $pdo
	 * @return Desk
	 */
	public static function fetchAll($pdo) {
		$result = $pdo->query("SELECT * FROM desk ORDER BY desk_id")
				->fetchAll(\PDO::FETCH_CLASS, Desk::class);
		return $result;
	}
	
	/**
	 * 
	 * @param \PDO $pdo
	 * @param mixed[] $desks
	 */
	public static function insertMany($pdo, $desks){
		$statement = $pdo->prepare('INSERT INTO `desk` (`desk_id`, `capacity`) VALUES (:desk_id, :capacity)');
		
		$return = ['added' => 0, 'duplicate' => 0];
		foreach ($desks as $desk){
			if(!$statement->execute($desk)){
				DB_Service::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString, $desk);
				if($statement->errorCode() == '23000'){
					$return['duplicate']++;
				}
				continue;
			}
			$return['added']++;
		}
		return $return;
	}
	
	/**
	 * 
	 * @param \PDO $pdo
	 * @param mixed[] $desks
	 */
	public static function updateMany($pdo, $desks) {
		$sql = 'UPDATE `desk` SET '
				. 'capacity = :capacity '
				. 'WHERE desk_id = :desk_id ';
		
		$statement = $pdo->prepare($sql);
		
		
		$errors = [];
		$changedRows = 0;
		foreach($desks as $desk){
			echo '.';
			if(!$statement->execute($desk)){
				$errors[] = ['error' => $statement->errorInfo(), 'pars' => $desk];
				continue;
			}
			
			$changedRows++;
		}
		
		foreach($errors as $err){
			DB_Service::logError($err['error'], __CLASS__."::".__FUNCTION__, $statement->queryString, $err['pars']);
		}
		
		return $changedRows;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param int[] $desk_ids
	 */
	public static function deleteMany($pdo, $desk_ids) {
		$statement = $pdo->prepare('DELETE FROM `desk` WHERE desk_id IN (:desk_id) ');
		$pars = ['desk_id' => implode(', ', $desk_ids)];
		
		if(!$statement->execute($pars)){
			DB_Service::logError($statement->errorInfo(), __CLASS__."::".__FUNCTION__, $statement->queryString, $pars);
			return false;
		}
		
		return $statement->columnCount();
	}
	
	public static function fromPOST($class = null) {
		return parent::createFromPost(self::class);
	}

		var $desk_id;
	var $capacity;
	
	public function getId() {
		return $this->desk_id;
	}
	
	public function getCapacity() {
		return $this->capacity;
	}

	public function setId($desk_id) {
		$this->desk_id = $desk_id;
	}

	public function setCapacity($capacity) {
		$this->capacity = $capacity;
	}

}
