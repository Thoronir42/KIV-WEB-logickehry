<?php

namespace controllers;

use \model\ImageManager,
	\model\MailManager;
use \model\database\tables as Tables,
	\model\database\views as Views;

class SpravaController extends Controller {

	public static function getDefaultAction() {
		return "hry";
	}

	protected function buildSubmenu() {
		$menu = [];
		$menu[] = ["urlParams" => ["controller" => "sprava", "action" => "hry"],
			"label" => "Hry"];
		$menu[] = ["urlParams" => ["controller" => "sprava", "action" => "inventar"],
			"label" => "Inventář"];
		if ($this->user->isAdministrator()) {
			$menu[] = ["separator" => true];
			$menu[] = ["urlParams" => ["controller" => "sprava", "action" => "uzivatele"],
				"label" => "Uživatelé"];
			$menu[] = ["urlParams" => ["controller" => "sprava", "action" => "ovladaciPanel"],
				"label" => "Ovládací panel"];
			$menu[] = ["urlParams" => ["controller" => "sprava", "action" => "hromadnyMail"],
				"label" => "Hromadný mail"];
		}
		return $menu;
	}

	public function startUp() {
		parent::startUp();
		if (!$this->user->isSupervisor()) {
			$this->message("Do sekce Správa nemáte přístup", \libs\MessageBuffer::LVL_WAR);
			$this->redirectPars(Controller::DEFAULT_CONTROLLER);
		}
	}

	public function renderHry() {
		$this->addCss("hra.css");
		$this->addCss('input-specific.css');
		$this->addJs('input-specific.js');

		$this->template['pageTitle'] = "Správa her";
		$this->template['insert_game_form_action'] = ['controller' => 'sprava', 'action' => 'pridatHru'];
		$this->template['gpr'] = 3;
		$this->template['game_edit_form_action'] = ['controller' => 'sprava', 'action' => 'upravitHru'];
		$this->template['mailLink'] = ['controller' => 'sprava', 'action' => 'hromadnyMail', 'id' => 0];


		$games = Views\GameTypeExtended::fetchAll($this->pdo);
		foreach ($games as $key => $g) {
			$path = $this->urlGen->img(ImageManager::get(sprintf("game_%03d", $g->game_type_id)));
			$games[$key]->picture_path = $path;
		}
		$this->template['games'] = $games;
	}

	public function doUpravitHru() {
		$keepPicture = $this->getParam('keepPicture', INPUT_POST);
		$gameType = Tables\GameType::fromPOST();
		if (!$gameType->readyForInsert()) {
			$this->message("Nebylo možné přidat hru, nebyla vyplněna následující pole: " . $gameType->getMissingParameters());
			$this->redirectPars('sprava', 'hry');
		}
		var_dump($gameType);

		if (Tables\GameType::update($this->pdo, $gameType->asArray())) {
			$this->message("Úpravy na hře $gameType->game_name $gameType->game_subtitle byly úspěšně uloženy", \libs\MessageBuffer::LVL_SUC);
		} else {
			$this->message("Úpravy na hře $gameType->game_name $gameType->game_subtitle se nepodařilo uložit", \libs\MessageBuffer::LVL_WAR);
		}

		if (!$keepPicture) {
			$imgRes = ImageManager::put("picture", sprintf("game_%03d", $gameType->game_type_id));
			if (!$imgRes['result']) {
				$this->message($imgRes['message'], \libs\MessageBuffer::LVL_WAR);
			}
		}
		$this->redirectPars('sprava', 'hry');
	}

	public function doPridatHru() {
		$nextId = Tables\GameType::nextId($this->pdo);
		$gameType = Tables\GameType::fromPOST();
		if (!$gameType->readyForInsert()) {
			$this->message("Nebylo možné přidat hru, nebyla vyplněna následující pole: " . $gameType->getMissingParameters());
			$this->redirectPars();
		}
		$pars = $gameType->asArray();
		$pars['game_type_id'] = $nextId;
		if (!Tables\GameType::insert($this->pdo, $pars)) {
			$this->message("Nebylo možné přidat hru na úrovni databáze", \libs\MessageBuffer::LVL_WAR);
			$this->redirectPars('sprava', 'hry');
		} else {
			$this->message("Hra $gameType->game_name byla úspěšně přidána do databáze", \libs\MessageBuffer::LVL_SUC);
		}

		$imgRes = ImageManager::put("picture", sprintf("game_%03d", $nextId));
		if (!$imgRes['result']) {
			$this->message($imgRes['message'], \libs\MessageBuffer::LVL_WAR);
		}
		$this->redirectPars('sprava', 'hry');
	}

	public function renderUzivatele() {
		if (!$this->user->isAdministrator()) {
			$this->message("Do správy uživatelů nemáte přístup.", \libs\MessageBuffer::LVL_WAR);
			$this->redirectPars("sprava", $this->getDefaultAction());
		}

		$this->template['pageTitle'] = "Správa registrovaných uživatelů";
		$this->template['users'] = Views\UserExtended::fetchAll($this->pdo);
		$this->template['actions'] = $this->buildUserActions();
		$this->template['tmpUrl'] = ['controller' => 'sprava', 'action' => 'nan'];
	}

	private function buildUserActions() {
		$act = [];
		$act['supervisor'] = [
			'add' => ['url' => 'addSupervisor', 'glyph' => 'glyphicon-pencil', 'tooltip' => 'Povýšit na správce', 'class' => 'add'],
			'remove' => ['url' => 'removeSupervisor', 'glyph' => 'glyphicon-pencil', 'tooltip' => 'Odebrat pravomoce správce', 'class' => 'remove']
		];

		return ['count' => count($act), 'list' => $act];
	}

	public function doAddSupervisor() {
		$this->setUserRole(Tables\User::ROLE_SUPERVISOR);
	}

	public function doRemoveSupervisor() {
		$this->setUserRole(Tables\User::ROLE_USER);
	}

	private function setUserRole($role) {
		$orion_login = $this->getParam("orion_login");

		$targetUser = Views\UserExtended::fetch($this->pdo, $orion_login);
		if ($targetUser->isAdministrator()) {
			$this->message("Administrátoři si vzájemně nemohou upravovat role. Pokud máte problém s $orion_login, kontaktujte prosím CIV.", \libs\MessageBuffer::LVL_DNG);
			$this->redirectPars('sprava', 'uzivatele');
		}

		if (Tables\User::setUserRole($this->pdo, $orion_login, $role)) {
			if ($role == Tables\User::ROLE_SUPERVISOR) {
				$this->message("Uživateli $orion_login byl přidělen statut správce");
			} else {
				$this->message("Uživateli $orion_login byl odebrán statut správce");
			}
		} else {
			$this->message("Nastala chyba při úpravě statutu uživatele $orion_login", \libs\MessageBuffer::LVL_DNG);
		}
		$this->redirectPars('sprava', 'uzivatele');
	}

	public function renderInventar() {
		$retired = $this->getParam("retired");
		$this->addCss("hra.css");
		$this->template['pageTitle'] = "Správa evidovaných herních krabic";
		$this->template['gpr'] = 2;
		$this->template['ipr'] = 2;


		$games = Views\GameBoxExtended::fetchAll($this->pdo);
		$gamesSrt = [];
		foreach ($games as $g) {
			if (!isset($gamesSrt[$g->game_type_id])) {
				$gamesSrt[$g->game_type_id] = $g->asArray();
				$gamesSrt[$g->game_type_id]['tracking_codes'] = [];
			}
			if ($g->tracking_code && (!$g->retired || $retired)) {
				$gamesSrt[$g->game_type_id]['tracking_codes'][] = $g;
			}
		}
		$this->template['retireAction'] = 'retireBox';
		$this->template['insertAction'] = 'insertBox';
		$this->template['games'] = $gamesSrt;
	}

	public function renderOvladaciPanel() {
		$this->template['xml_inventory'] = ['controller' => 'xml', 'action' => 'inventory'];
		$this->template['xml_reservations'] = ['controller' => 'xml', 'action' => 'reservations'];
		$this->template['operator_enabled'] = \Dispatcher::ENABLE_OPERATOR;
		$this->template['SQL_files'] = \libs\Operator::getSQLfiles();
		$this->template['SQL_action'] = ['controller' => 'operator', 'action' => 'SQL'];
		$this->template['letiste_links'] = $this->makeLetisteLinks();
	}

	private function makeLetisteLinks() {
		$return = [];
		$return[] = ['label' => 'Rezervace', 'url' => ['controller' => 'letiste', 'action' => 'rezervace']];
		return $return;
	}

	public function renderHromadnyMail() {
		$this->addCss('input-specific.css');
		$this->addJs('input-specific.js');

		$this->template['default_subject'] = MailManager::getDefaultSubject();
		$this->template['send_url'] = ['controller' => 'sprava', 'action' => 'poslatMail'];
		$this->template['games'] = Views\GameTypeExtended::fetchAll($this->pdo);
		$this->template['active'] = $this->getParam('id');
	}

	public function doPoslatMail() {
		$gid = $this->getParam('game_type_id', INPUT_POST);
		$subject = $this->getParam('subject', INPUT_POST);
		$body = $this->getParam('mail_body', INPUT_POST);

		$users = Views\Subscription::fetchUsersByGame($this->pdo, $gid);

		$result = MailManager::send($users, $body, $subject);
		if ($result['result']) {
			$this->message($result['message'], \libs\MessageBuffer::LVL_SUC);
		} else {
			$this->message($result['message'], \libs\MessageBuffer::LVL_DNG);
		}

		$this->redirectPars('sprava', $this->getDefaultAction());
	}

}
