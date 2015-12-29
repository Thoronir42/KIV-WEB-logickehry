<?php
namespace controllers;


class LetisteController extends Controller{
	
	public static function getDefaultAction(){ return 'rezervace'; }
	
	public function __construct($support){
		parent::__construct($support);
		$this->layout = "letiste.twig";
	}
	
	public function renderRezervace(){
		
	}
	
}
