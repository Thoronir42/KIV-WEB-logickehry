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
		if(!$this->user->isLoggedIn()){
			$this->message('Pro psaní ohlasů musíte být přihlášeni.');
			$this->redirectPars();
		}
		if(!self::FEEDBACK_ENABLED){
			$this->message('Ohlasy zpětné vazby nejsou v tento moment povoleny');
			$this->redirectPars();
		}
		$this->template['pageTitle'] = 'Zpětná vazba';
	}

	public function renderPridat(){
		$this->template['feedbackItems'] = Tables\Feedback::fetchAll($this->pdo);
		$this->template['formAction'] = ['controller' => 'ohlas', 'action' => 'vlozit'];
		
	}
	public function doVlozit(){
		$fb = Tables\Feedback::fromPOST();
		$fb->user_id = $this->user->user_id;
		$fb->created = date(\model\DatetimeManager::DB_FULL);
		if(Tables\Feedback::insert($this->pdo, $fb->asArray())){
			$this->message('Vaše zpětná vazba byla úspěšně uložena. Děkujeme.');
		} else {
			$this->message('Při ukládání vaší zpětné vazby nastaly potíže.', \libs\MessageBuffer::LVL_WAR);
		}
		$this->redirectPars('ohlas', 'pridat');
	}

}
