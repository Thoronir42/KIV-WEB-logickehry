<?php
include __DIR__.'/libs/autoloader.php';

// Prepare session
\libs\SessionManager::run();

//	Setup PDO connection
$cfgFile = __DIR__.("/config/database.cfg.php");
$cfg = include "$cfgFile";
$cfg['password'] = isset($cfg['password']) ? $cfg['password'] : null;
$pdo = new PDO("mysql:host=$cfg[host];dbname=$cfg[db_name];charset=utf8", $cfg['user'], $cfg['password']);

// Setup Twig templating
$loader = new Twig_Loader_Filesystem(__DIR__.'/templates/');
$twig = new Twig_Environment($loader, array(
    /*'cache' => __DIR__.'/cache/',*/
	'debug' => true,
));
$twig->addExtension(new Twig_Extension_Debug());

// Setup URL generator
$protocol = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']))
	? "https" : "http";
$prefix =  $protocol."://$_SERVER[SERVER_NAME]/"; 
$urlGen = new libs\URLgen($prefix);

// Prepare message buffer
$buffer = libs\MessageBuffer::getInstance("CLH_alert_log");

// Prepare dispatcher
$dispatcher = new Dispatcher($pdo, $twig, $urlGen, $buffer);

$controller = filter_input(INPUT_GET, 'controller');
$action = filter_input(INPUT_GET, 'action');

$dispatcher->dispatch($controller, $action);
