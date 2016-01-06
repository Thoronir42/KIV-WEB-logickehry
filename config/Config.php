<?php

namespace config;

/**
 * Description of Config
 *
 * @author Stepan
 */
class Config {

	/**
	 * 
	 * @return \PDO
	 */
	public static function getPDO() {
		$cfg = include "database.cfg.php";
		$cfg['password'] = isset($cfg['password']) ? $cfg['password'] : null;
		return new \PDO("mysql:host=$cfg[host];dbname=$cfg[db_name];charset=utf8", $cfg['user'], $cfg['password']);
	}

	/**
	 * 
	 * @param String $templateDir
	 * @return \Twig_Environment
	 */
	public static function getTwig($templateDir) {
		$loader = new \Twig_Loader_Filesystem($templateDir);
		$twig = new \Twig_Environment($loader, array(
			/* 'cache' => __DIR__.'/cache/', */
			'debug' => true,
		));
		$twig->addExtension(new \Twig_Extension_Debug());
		return $twig;
	}

	/**
	 * 
	 * @return \libs\URLgen
	 */
	public static function getURLgen() {
		$protocol = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])) ? "https" : "http";
		$prefix = $protocol . "://$_SERVER[SERVER_NAME]/";
		return new \libs\URLgen($prefix);
	}

	/**
	 * 
	 * @return type
	 */
	public static function getMessageBuffer() {
		return \libs\MessageBuffer::getInstance("CLH_alert_log");
	}

	/**
	 * 
	 * @return \Dispatcher
	 */
	public static function createDispatcher() {
		$pdo = Config::getPDO();
		$twig = Config::getTwig(__DIR__ . '/../templates/');
		$urlGen = Config::getURLgen();
		$buffer = Config::getMessageBuffer();

		return new \Dispatcher($pdo, $twig, $urlGen, $buffer);
	}

}
