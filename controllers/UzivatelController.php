<?php
namespace controllers;

/**
 * Description of UzivatelControler
 *
 * @author Stepan
 */
class UzivatelController extends Controller{
    
	var $block_sauce = true;
	
	public function __construct(){
		parent::__construct();
		$this->layout = "ajax.twig";
	}
	
	public function renderZmenaUdaju(){
		
	}
	
}
