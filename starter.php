<?php

include __DIR__ . '/libs/Loader.php';

use libs\SessionManager;
use libs\MessageBuffer;

SessionManager::run($demo = false);

\model\services\DB_Service::$message_buffer = MessageBuffer::getInstance("PDO_error_log");

$dispatcher = Loader::createDispatcher();
$dispatcher->dispatch();
