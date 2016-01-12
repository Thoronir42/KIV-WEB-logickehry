<?php

include 'Twig/Autoloader.php';
Twig_Autoloader::register(true);

include 'PHPMailer/PHPMailerAutoload.php';

spl_autoload_register('Loader::NamespaceLoader');
spl_autoload_register('Loader::LibsLoader');

spl_autoload_register('Twig_Autoloader::autoload');

/**
 * Description of Config
 *
 * @author Stepan
 */
class Loader {

	/**
	 * 
	 * @return \PDO
	 */
	public static function getPDO() {
		$cfg = include "../config/database.cfg.php";
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
		$pdo = Loader::getPDO();
		$twig = Loader::getTwig(__DIR__ . '/../templates/');
		$urlGen = Loader::getURLgen();
		$buffer = Loader::getMessageBuffer();

		return new \Dispatcher($pdo, $twig, $urlGen, $buffer);
	}
	
	// ### Autoloader functions
	
	public static function NamespaceLoader($class) {
		if (self::tryInclude(__DIR__ . "/../" . $class)) {
			return true;
		}

		return false;
	}

	public static function LibsLoader($class) {
		$path = __DIR__ . "/$class";
		if (self::tryInclude($path)) {
			return true;
		}

		$path.="/$class";
		if (self::tryInclude($path)) {
			return true;
		}
		return false;
	}

	public static function ControllerLoader($class) {
		$path = __DIR__ . "/../controllers/";
		if (self::tryInclude($path . $class)) {
			return true;
		}

		return false;
	}

	public static function ModelLoader($class) {
		$path = __DIR__ . "/../model/";
		if (self::tryInclude($path . $class)) {
			return true;
		}

		$path.="database/";
		if (self::tryInclude($path . "/tables/" . $class)) {
			return true;
		}
		if (self::tryInclude($path . "/views/" . $class)) {
			return true;
		}

		return false;
	}

	private function tryInclude($path) {
		if (file_exists($path . ".php")) {
			include $path . ".php";
			return true;
		}
		return false;
	}

}
