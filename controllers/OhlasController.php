<?php

namespace controllers;

use \model\database\tables as Tables,
	\model\database\views as Views;

/**
 * Description of AjaxController
 *
 * @author Stepan
 */
class OhlasController extends Controller {
	
	const FEEDBACK_ENABLED = true;
	
	public function __construct($support) {
		parent::__construct($support);
	}
	
	public function startUp() {
		parent::startUp();
		if(!self::FEEDBACK_ENABLED){
			$this->message('Ohlasy zpětné vazby nejsou v tento moment povoleny');
			$this->redirectPars();
		}
	}

	public function renderPridat(){
		
	}

}
