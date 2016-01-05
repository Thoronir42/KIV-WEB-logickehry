<?php

namespace controllers;

use \libs\Operator;

/**
 * Description of OperatorController
 *
 * @author Stepan
 */
class OperatorController extends Controller {

	public function startUp() {
		parent::startUp();
		if (!\Dispatcher::ENABLE_OPERATOR) {
			$this->message('Operátor je momentálně vypnutý!', \libs\MessageBuffer::LVL_INF);
			$this->redirectPars();
		}

		if (!$this->user->isAdministrator()) {
			$this->message('Do sekce operator mohou přistupovat pouze administrátoři!', \libs\MessageBuffer::LVL_DNG);
			$this->redirectPars();
		}
	}

	public function doSQL() {
		$filename = $this->getParam('filename', INPUT_POST);
		$result = Operator::injectSQL($this->pdo, $filename);
		if (!$result) {
			$this->message("SQL injekce proběhla úspěšně", \libs\MessageBuffer::LVL_SUC);
			$this->redirectPars();
		} else {
			$this->message($result, \libs\MessageBuffer::LVL_WAR);
			$this->redirectPars();
		}
	}

}
