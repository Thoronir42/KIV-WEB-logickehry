<?php

spl_autoload_register('Autoloader::ClassLoader');
spl_autoload_register('Autoloader::ModelLoader');

class Autoloader{
    
    public static function ClassLoader($class){
        $path = __DIR__."/$class";
        if(!self::tryInclude($path)){
            $path.="/$class";
            if(!self::tryInclude($path)){
                return false;
            }
        }
        return true;
    }
    
    public static function ModelLoader($class){
        $path = __DIR__."/../models/".$class;
        if(!self::tryInclude($path)){
            return false;
        }
        return true;
    }
    
    private function tryInclude($path){
        if(file_exists($path.".php")){            
            include $path.".php";
            return true;
        }
        return false;
    }
}

