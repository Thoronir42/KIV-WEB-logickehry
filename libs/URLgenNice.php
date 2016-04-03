<?php

namespace libs;

use libs\ImageManager,
	config\Config;

class URLgenNice extends URLgen{

	public function __construct($prefix) {
		parent::__construct($prefix);
	}
		
	protected function handleUrl(){
		$redirect = false;
		$url = filter_input(INPUT_GET, 'q');
		$parts = explode(self::ADDR_SEP, $url);
		if (count($parts) < 2) {
			$redirect = true;
		} else {
			$controller = $parts[0];
			$action = $parts[1];
		}
		return ['controller' => $controller, 'action' => $action, 'redirect' => $redirect];
	}

	public function url($params = null) {
		$return = $this->urlPrefix;
		if (empty($params)) {
			return $return;
		}
		if (isset($params['controller'])) {
			$return .= $params['controller'] . self::ADDR_SEP;
			unset($params['controller']);
		}
		if (isset($params['action'])) {
			$return .= $params['action'] . self::ADDR_SEP;
			unset($params['action']);
		}


		$first = true;
		foreach ($params as $parKey => $parVal) {
			$return.=($first ? "?" : "&") . "$parKey=$parVal";
			$first = false;
		}
		return $return;
	}

}
