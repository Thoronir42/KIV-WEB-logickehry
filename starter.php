<?php
include __DIR__.'/libs/autoloader.php';

//	Setup PDO connection
$cfgFile = __DIR__.((true) ? "/config/database.local.php" : "/config/database.php");
$pdoCfg = include "$cfgFile";
$pdow = PDOwrapper::getConnection($pdoCfg);

// Setup Twig templating
$loader = new Twig_Loader_Filesystem(__DIR__.'/templates/');
$twig = new Twig_Environment($loader, array(
    /*'cache' => __DIR__.'/cache/',*/
));

// Setup URL generator
$protocol = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']))
	? "https" : "http";
$prefix =  $protocol."://$_SERVER[SERVER_NAME]/"; 
$urlGen = new libs\URLgen($prefix);

// Prepare dispatcher
$dispatcher = new Dispatcher($pdow, $twig, $urlGen);

$controller = filter_input(INPUT_GET, 'controller') ?: "vypis";
$action = filter_input(INPUT_GET, 'action') ?: "vse";

$dispatcher->dispatch($controller, ["action" => $action]);
