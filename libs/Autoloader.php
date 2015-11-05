<?php
include __DIR__.'/Twig/Autoloader.php';
Twig_Autoloader::register(true);

spl_autoload_register('Autoloader::LibsLoader');
spl_autoload_register('Autoloader::NamespaceLoader');

spl_autoload_register('Autoloader::ModelLoader');

spl_autoload_register('Twig_Autoloader::autoload');



class Autoloader{
    
	public static function NamespaceLoader($class){
		if (self::tryInclude(__DIR__."/../".$class)){ return true; }
		
		return false;
	}
	
    public static function LibsLoader($class){
        $path = __DIR__."/$class";
        if(self::tryInclude($path)){ return true; }
		
		$path.="/$class";
		if(self::tryInclude($path)){
			return true;
		}
        return false;
    }
    
	public static function ControllerLoader($class){
		$path = __DIR__."/../controllers/";
		if(self::tryInclude($path.$class)){ return true; }
		
		return false;
	}
	
    public static function ModelLoader($class){
        $path = __DIR__."/../model/";
		echo "model: ".$class."<br/>";
        if(self::tryInclude($path.$class)){ return true; }
		
		$path.="database/";
		if(self::tryInclude($path."/tables/".$class)){ return true; }
		if(self::tryInclude($path."/views/".$class)){ return true; }
		
        return false;
    }
    
    private function tryInclude($path){
        if(file_exists($path.".php")){            
            include $path.".php";
            return true;
        }
        return false;
    }
}

