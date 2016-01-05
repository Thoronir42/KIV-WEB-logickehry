<?php
include __DIR__.'/libs/autoloader.php';

use config\Config;

// Prepare session
\libs\SessionManager::run();
$_SESSION['demo'] = true;

$dispatcher = Config::createDispatcher();
$controller = filter_input(INPUT_GET, 'controller');
$action = filter_input(INPUT_GET, 'action');

$dispatcher->dispatch($controller, $action);
