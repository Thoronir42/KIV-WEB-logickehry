<?php
include __DIR__.'/libs/autoloader.php';

// Prepare session
session_start();

//	Setup PDO connection
$cfgFile = __DIR__.((true) ? "/config/database.local.php" : "/config/database.php");
$pdoCfg = include "$cfgFile";
$pdow = libs\PDOwrapper::getConnection($pdoCfg);

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
$dispatcher = new Dispatcher($pdow, $twig, $urlGen, $buffer);

$controller = filter_input(INPUT_GET, 'controller') ?: "vypis";
$action = filter_input(INPUT_GET, 'action') ?: "hry";

$dispatcher->dispatch($controller, $action, []);
