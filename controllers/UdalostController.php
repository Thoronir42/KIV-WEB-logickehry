<?php

namespace controllers;

/**
 * Description of UdalostController
 *
 * @author Stepan
 */
class UdalostController extends Controller {

	public function doPridat() {
		$event = Tables\Event::fromPOST();
		var_dump($event);
		die;
	}

	public function renderDetail() {
		$id = $this->getParam("id");
	}

}
