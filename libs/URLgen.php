<?php
namespace libs;


class URLgen{
	
	var $urlPrefix;
	
	public function __construct(){
		$protocol = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']))
        ? "https" : "http";
		$this->urlPrefix =  $protocol."://$_SERVER[SERVER_NAME]/"; 
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
	
	public function getCss($cssFile){
		return $this->urlPrefix."css/".$cssFile;
	}
	public function getScript($jsFile){
		return $this->urlPrefix."js/".$jsFile;
	}
}

