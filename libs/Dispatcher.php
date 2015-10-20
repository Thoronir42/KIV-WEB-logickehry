<?php
namespace clh;

/**
 * Description of Dispatcher
 *
 * @author Stepan
 */
class Dispatcher {
    
    public static function getControler($controlerName = "Home"){
        switch($controlerName){
            default: 
                return new ErrorControler();
            case "Home":
                return new HomeControler();
            case "Login":
                return new LoginControler();
        }
    }
    
    public static function doControlerAction($controler, $action){
        
    }
}
