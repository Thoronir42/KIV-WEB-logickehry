<?php
namespace controllers;

/**
 * Description of HomeControler
 *
 * @author Stepan
 */
class ErrorController extends Controller{
    
	public function __construct(){
		parent::__construct();
		
	}
	
	public function startUp(){
		parent::startUp();
		$this->layout = 'layout.twig';
	}
	
	public function renderNoControllerFound(){
		
	}
	
	public function renderNotRecognizedAction(){
		
	}
	
	public function renderNoTemplate(){
		
	}
	
	public function renderNoRenderFound(){
		
	}
	
}
