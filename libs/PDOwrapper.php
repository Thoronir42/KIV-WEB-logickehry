<?php
use model\database\views as Views;


class PDOwrapper{
    /** @var PDO */
    static private $connection;
    
    public static function connect($db){
        $db['password'] = isset($db['password']) ? $db['password'] : null;
        self::$connection = new PDO("mysql:host=$db[host];dbname=$db[db_name]", $db['user'], $db['password']);
    }
	
	public static function getGamesWithScores(){
		$gtws = new Views\GameTypeWithScore();
		$sql = self::$connection->query("SELECT * FROM `game_type_w_score`");
		
		
		return $sql->fetchAll(PDO::FETCH_ASSOC);
	}
	
}