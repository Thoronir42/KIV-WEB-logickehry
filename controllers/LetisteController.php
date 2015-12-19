<?php
namespace controllers;


class LetisteController extends Controller{
	
	public static function getDefaultAction(){ return 'rezervace'; }
	
	public function __construct(){
		parent::__construct();
		$this->layout = "letiste.twig";
	}
	
	public function renderRezervace(){
		
	}
	
}
