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
	
	public function doRetireBox(){
		if(!$this->user->isSupervisor()){ return 'false'; }
		
		$code = $this->getParam("code");
		$box = $this->pdoWrapper->retireBox($code);
		$this->template['response'] = $box ? $code : 'false';
	}
	
	public function doInsertBox(){
		if(!$this->user->isSupervisor()){ return 'false'; }
		$code = $this->getParam("code");
	}
}
