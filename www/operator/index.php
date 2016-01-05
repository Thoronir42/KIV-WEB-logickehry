<?php

include '../../libs/Autoloader.php';
libs\SessionManager::run();

$dispatcher = config\Config::createDispatcher();
$controller = $dispatcher->getControllerInstance('rezervace');

if (!Dispatcher::ENABLE_OPERATOR) {
	$controller->message('Operátor je momentálně vypnutý!', \libs\MessageBuffer::LVL_INF);
	$controller->redirectPars();
}

if (!$controller->user->isLoggedIn() || !$controller->user->isAdministrator()) {
	$controller->message('Do sekce operator mohou přistupovat pouze administrátoři!', \libs\MessageBuffer::LVL_DNG);
	$controller->redirectPars();
}
$action = $controller->urlGen->getContAct()['action'];

switch ($action) {
	default:
		$controller->message("Neplatná akce '$action' operátoru", \libs\MessageBuffer::LVL_WAR);
		$controller->redirectPars();
		break;
	case 'injectSQL':
		$result = operator\Operator::injectSQL('db_logickehry', $pdo);
		if (!$result) {
			$controller->message("SQL injekce proběhla úspěšně", \libs\MessageBuffer::LVL_SUC);
			$controller->redirectPars();
		} else {
			$controller->message($result, \libs\MessageBuffer::LVL_WAR);
			$controller->redirectPars();
		}
		break;
}

