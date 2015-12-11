<?php
namespace controllers;

/**
 * Description of HomeControler
 *
 * @author Stepan
 */
class AjaxController extends Controller{
    
	var $block_sauce = true;
	
	public function __construct(){
		parent::__construct();
		$this->layout = "ajax.twig";
	}
	
	public function startUp(){
		parent::startUp();
		$this->layout = 'layout.twig';
	}
	
	public function doStuff(){
		$id=$this->getParam("id");
		$this->template['response'] = $id;
	}
}
