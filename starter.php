<?php

include __DIR__ . '/libs/Loader.php';

use libs\SessionManager;
use libs\MessageBuffer;

SessionManager::run($demo = false);

\model\database\DB_Entity::$message_buffer = MessageBuffer::getInstance("PDO_error_log");

$dispatcher = Loader::createDispatcher();
$dispatcher->dispatch();
