<?php
namespace controllers;

/**
 * Description of AjaxController
 *
 * @author Stepan
 */
class AjaxController extends Controller{
    
	var $block_sauce = true;
	
	public function __construct(){
		parent::__construct();
		$this->layout = "ajax.twig";
	}
	
	public function doStuff(){
		$id=$this->getParam("id");
		$this->template['response'] = $id;
	}
	
	public function doRetireBox(){
		$code = $this->getParam("code");
		$box = $this->pdoWrapper->fetchBox($code);
		$this->template['response'] = $box ? $code : 'false';
	}
}
