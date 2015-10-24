<?php

class PDOwrapper{
    /** @var PDO */
    static private $connection;
    
    public static function connect($db){
        $db['password'] = isset($db['password']) ? $db['password'] : null;
        self::$connection = new PDO("mysql:host=$db[host];dbname=$db[db_name]", $db['user'], $db['password']);
    }
    
}