<?php
namespace libs;


class URLgen{
	
	var $urlPrefix;
	
	public function __construct($prefix){
		$this->urlPrefix =  $prefix;
	}
	
	public function getUrl($params){
		$return = $this->urlPrefix;
		$first = true;
		foreach($params as $parKey => $parVal){
			$return.=($first ?"?":"&")."$parKey=$parVal";
			$first = false;
		}
		return $return;
	}
	
	public function getCss($file){
		return $this->urlPrefix."css/".$file;
	}
	public function getJs($file){
		return $this->urlPrefix."js/".$file;
	}
	public function getImg($file){
		return $this->urlPrefix."images/".$file;
	}
}

