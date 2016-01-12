<?php

include __DIR__ . '/libs/Loader.php';

use libs\SessionManager;

SessionManager::run($demo = false);

$dispatcher = Loader::createDispatcher();
$dispatcher->dispatch();
