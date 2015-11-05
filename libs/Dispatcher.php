<?php

/**
 * Description of Dispatcher
 *
 * @author Stepan
 */
class Dispatcher {
    
    public function __construct($params) {
        
    }
    
    public static function getControler($controlerName = "Rezervace"){
        switch($controlerName){
            default: 
                return new controllers\ErrorController();
			case "vypis":
				return new controllers\VypisController();
            case "rezervace":
                return new controllers\HomeController();
            case "login":
                return new controllers\LoginController();
        }
    }
    /**
     * 
     * @param Controler $controler
     * @param type $action
     */
    public function doControlerAction($controler, $action){
        $controllerClass = new ReflectionClass($controler);
    }
}
