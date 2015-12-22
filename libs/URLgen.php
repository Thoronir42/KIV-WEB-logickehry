<?php
namespace libs;

use model\ImageManager;

class URLgen{
	
	var $urlPrefix;
	
	public function __construct($prefix){
		$this->urlPrefix =  $prefix;
	}
	
	public function url($params){
		$return = $this->urlPrefix;
		if(!$params){ return $return; }
		$first = true;
		foreach($params as $parKey => $parVal){
			$return.=($first ?"?":"&")."$parKey=$parVal";
			$first = false;
		}
		return $return;
	}
	
	public function loginUrl(){
		return $this->urlPrefix."webauth/";
	}
	
	public function css($file){
		return $this->urlPrefix."css/".$file;
	}
	public function js($file){
		return $this->urlPrefix."js/".$file;
	}
	public function img($file){
		return $this->urlPrefix."images/".$file;
	}
	
	public function gImg($game_type_id){
		$filename = sprintf("game_%03d", $game_type_id);
		$path = ImageManager::get($filename);
		return $this->img($path);
	}
	
	public function gDet($game_type_id){
		return $this->url([	'controller'=> 'vypis',
							'action'	=> 'detailHry',
							'id'		=> $game_type_id]);
	}
}

