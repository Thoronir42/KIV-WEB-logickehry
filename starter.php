<?php

include __DIR__ . '/libs/autoloader.php';

use config\Config;
use libs\SessionManager;

SessionManager::run($demo = false);

$dispatcher = Config::createDispatcher();
$dispatcher->dispatch();
