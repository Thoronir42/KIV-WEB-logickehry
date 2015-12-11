<?php
namespace libs;


class URLgen{
	
	var $urlPrefix;
	
	public function __construct($prefix){
		$this->urlPrefix =  $prefix;
	}
	
	public function url($params){
		$return = $this->urlPrefix;
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
}

