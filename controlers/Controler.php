<?php
namespace clh;
/**
 * Description of Controler
 *
 * @author Stepan
 */
class Controler{
    
    protected $data;
    
    /** @var \TemplateContainer */
    protected $template;
    
    public function __construct() {
        $this->data = [];
        
    }
    
    public function showDefault(){
        
    }
    
    public function redirect($location){
        \header("Location: /$location");
	\header("Connection: close");
    }

}
