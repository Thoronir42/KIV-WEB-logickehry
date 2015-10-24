<?php
namespace clh;

/**
 * Description of Dispatcher
 *
 * @author Stepan
 */
class Dispatcher {
    
    
    
    public function __construct($params) {
        
    }
    
    public function getControler($controlerName = "Home"){
        switch($controlerName){
            default: 
                return new ErrorControler();
            case "Home":
                return new HomeControler();
            case "Login":
                return new LoginControler();
        }
    }
    /**
     * 
     * @param Controler $controler
     * @param type $action
     */
    public function doControlerAction($controler, $action){
        $controler = new ReflectionClass($controler);
    }
}
