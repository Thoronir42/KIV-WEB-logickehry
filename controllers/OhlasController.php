<?php

namespace controllers;

use \model\database\tables as Tables,
	\model\database\views as Views;
use libs\DatetimeManager;

use config\Config;

/**
 * Description of AjaxController
 *
 * @author Stepan
 */
class OhlasController extends Controller {

	
	const STATE_CLOSE = 'zavrit';
	const STATE_OPEN = 'otevrit';

	public function __construct($support) {
		parent::__construct($support);
	}

	public function startUp() {
		parent::startUp();
		if (!$this->user->isLoggedIn()) {
			$this->message('Pro psaní ohlasů musíte být přihlášeni.');
			$this->redirectPars();
		}
		if (!Config::FEEDBACK_ENABLED) {
			$this->message('Ohlasy zpětné vazby nejsou v tento moment povoleny');
			$this->redirectPars();
		}
		$this->template['pageTitle'] = 'Zpětná vazba';
	}

	public function renderPridat() {
		$this->template['feedbackItems'] = Tables\Feedback::fetchAll($this->pdo);
		$this->template['formAction'] = ['controller' => 'ohlas', 'action' => 'vlozit'];
		$this->template['detailedList'] = ['controller' => 'ohlas', 'action' => 'zobrazitVsechny'];
	}

	public function doVlozit() {
		$fb = Tables\Feedback::fromPOST();
		$fb->user_id = $this->user->user_id;
		$fb->created = date(DatetimeManager::DB_FULL);
		if (Tables\Feedback::insert($this->pdo, $fb->asArray())) {
			$this->message('Vaše zpětná vazba byla úspěšně uložena. Děkujeme.');
		} else {
			$this->message('Při ukládání vaší zpětné vazby nastaly potíže.', \libs\MessageBuffer::LVL_WAR);
		}
		$this->redirectPars('ohlas', 'pridat');
	}

	public function renderZobrazitVsechny() {
		if (!$this->user->isAdministrator()) {
			$this->message('Detailní výpis ohlasů je dostupný pouze administrátorům', \libs\MessageBuffer::LVL_WAR);
			$this->redirectPars();
		}
		$this->template['feedbackItems'] = Tables\Feedback::fetchAll($this->pdo);
		$this->template['closeLink'] = ['controller' => 'ohlas', 'action' => 'zmenitStav', 'co' => self::STATE_CLOSE, 'id' => 0];
		$this->template['openLink'] = ['controller' => 'ohlas', 'action' => 'zmenitStav', 'co' => self::STATE_OPEN, 'id' => 0];
	}

	public function doZmenitStav() {
		$co = $this->getParam('co');
		$id = $this->getParam('id');
		switch ($co) {
			default: return;
			case self::STATE_CLOSE:
				$result = Tables\Feedback::close($this->pdo, $id);
				break;
			case self::STATE_OPEN:
				$result = Tables\Feedback::open($this->pdo, $id);
				break;
		}
		$this->redirectPars('ohlas', 'zobrazitVsechny');
	}

}
