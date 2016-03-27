<?php

namespace controllers;

use libs\ImageManager,
	libs\MailManager;
use \model\database\tables as Tables,
	\model\database\views as Views;

class SpravaController extends Controller {

	const ALL_USERS_GT_ID = -1;

	public static function getDefaultAction() {
		return "hry";
	}

	protected function buildSubmenu() {
		$menu = [];
		$menu[] = ["urlParams" => ["controller" => "sprava", "action" => "hry"],
			"label" => "Hry"];
		$menu[] = ["urlParams" => ["controller" => "sprava", "action" => "inventar"],
			"label" => "Inventář"];
		$menu[] = ["urlParams" => ["controller" => "sprava", "action" => "stoly"],
			"label" => "Stoly"];
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
			$this->message->warning("Do sekce Správa nemáte přístup", \libs\MessageBuffer::LVL_WAR);
			$this->redirectPars(Controller::DEFAULT_CONTROLLER);
		}
	}

	public function renderHry() {
		$this->addCss("hra.css");
		$this->addCss('input-specific.css');
		$this->addJs('input-specific.js');

		$this->template['pageTitle'] = "Správa her";
		$this->template['insert_game_form_action'] = ['controller' => 'sprava', 'action' => 'pridatHru'];
		$this->template['col_game'] = 4;
		$this->template['game_edit_form_action'] = ['controller' => 'sprava', 'action' => 'upravitHru'];
		$this->template['mailLink'] = ['controller' => 'sprava', 'action' => 'hromadnyMail', 'id' => 0];
		$this->template['img_fileTypes'] = ImageManager::fileTypes();

		$this->template['games'] = Views\GameTypeExtended::fetchAllWithCounts($this->pdo);
	}

	public function doUpravitHru() {
		$keepPicture = $this->getParam('keepPicture', INPUT_POST);
		$gameType = Tables\GameType::fromPOST();
		if (!$gameType->readyForInsert()) {
			$this->message->warning("Nebylo možné přidat hru, nebyla vyplněna následující pole: " . $gameType->getMissingParameters());
			$this->redirectPars('sprava', 'hry');
		}
		$pars = $gameType->asArray();
		$id = $pars['game_type_id'];
		unset($pars['game_type_id']);

		if (Tables\GameType::update($this->pdo, $pars, $id)) {
			$this->message->success("Úpravy na hře " . $gameType->getFullName() . " byly úspěšně uloženy");
		} else {
			$this->message->warning("Úpravy na hře " . $gameType->getFullName() . " se nepodařilo uložit");
		}

		if (!$keepPicture) {
			$imgRes = ImageManager::put("picture", sprintf("game_%03d", $gameType->game_type_id));
			if (!$imgRes['result']) {
				$this->message->warning($imgRes['message']);
			}
		}
		$this->redirectPars('sprava', 'hry');
	}

	public function doPridatHru() {
		$nextId = Tables\GameType::nextId($this->pdo);
		$gameType = Tables\GameType::fromPOST();
		if (!$gameType->readyForInsert()) {
			$this->message->warning("Nebylo možné přidat hru, nebyla vyplněna následující pole: " . $gameType->getMissingParameters());
			$this->redirectPars();
		}
		$pars = $gameType->asArray();
		$pars['game_type_id'] = $nextId;
		if (!Tables\GameType::insert($this->pdo, $pars)) {
			$this->message->warning("Nebylo možné přidat hru na úrovni databáze");
			$this->redirectPars('sprava', 'hry');
		} else {
			$this->message->success("Hra $gameType->game_name byla úspěšně přidána do databáze");
		}

		$imgRes = ImageManager::put("picture", sprintf("game_%03d", $nextId));
		if (!$imgRes['result']) {
			$this->message->warning($imgRes['message']);
		}
		$this->redirectPars('sprava', 'hry');
	}

	public function renderUzivatele() {
		if (!$this->user->isAdministrator()) {
			$this->message->warning("Do správy uživatelů nemáte přístup.");
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
			$this->message->danger("Administrátoři si vzájemně nemohou upravovat role. Pokud máte problém s $orion_login, kontaktujte prosím CIV.");
			$this->redirectPars('sprava', 'uzivatele');
		}

		if (Tables\User::setUserRole($this->pdo, $orion_login, $role)) {
			if ($role == Tables\User::ROLE_SUPERVISOR) {
				$this->message->info("Uživateli $orion_login byl přidělen statut správce");
			} else {
				$this->message->info("Uživateli $orion_login byl odebrán statut správce");
			}
		} else {
			$this->message->warning("Nastala chyba při úpravě statutu uživatele $orion_login");
		}
		$this->redirectPars('sprava', 'uzivatele');
	}

	public function renderStoly() {
		$this->template['addForm'] = ['action' => ['controller' => 'sprava', 'action' => 'pridatStul']];
		
		$this->template['editForm'] = ['action' => ['controller' => 'sprava', 'action' => 'upravitStoly']];
		$this->template['editForm']['desks'] = Tables\Desk::fetchAll($this->pdo);
	}
	
	public function doPridatStul(){
		$desk = Tables\Desk::fromPOST();
		if(!$desk->readyForInsert()){
			$this->message->warning('Některé položky nebyly vyplněny správně');
			$this->redirectPars('sprava', 'stoly');
		}
		
		$desks = [
			['desk_id' => $desk->desk_id, 'capacity' => $desk->capacity]
				];
		$result = Tables\Desk::insertMany($this->pdo, $desks);
		if($result['added']){
			$this->message->success('Stůl byl úspěšně přidán');	
		}
		if($result['duplicate']){
			$this->message->warning('Stůl s číslem '.$desk->desk_id.' nemohl být přidán protože je již existuje');
		}
		
		$this->redirectPars('sprava', 'stoly');
	}
	
	public function doUpravitStoly(){
		$desks = ['delete' => [], 'update' => []];
		foreach($_POST as $key => $desk){
			$desk['desk_id'] = explode('_', $key)[1];
			
			$keep = !isset($desk['delete']);
			unset($desk['delete']);
			
			if($keep){
				$desks['update'][] = $desk;
			} else {
				$desks['delete'][] = $desk['desk_id'];
			}
		}
		
		
		$updated = Tables\Desk::updateMany($this->pdo, $desks['update']);
		if($updated > 0){
			$this->message->info("$updated stolů bylo upraveno.");
		}
		
		$deleted = Tables\Desk::deleteMany($this->pdo, $desks['delete']);
		if($deleted > 0){
			$this->message->info("$deleted stolů bylo smazáno.");
		}
		
		$this->redirectPars('sprava', 'stoly');
	}

	
	public function renderInventar() {
		$retired = $this->getParam("retired");
		$this->addCss("hra.css");
		$this->template['pageTitle'] = "Správa evidovaných herních krabic";
		$this->template['col_game'] = 4;
		$this->template['col_code'] = 12;


		$boxes = Views\GameBoxExtended::fetchAll($this->pdo);
		$games = Views\GameTypeExtended::fetchAll($this->pdo);
		$gamesSrt = [];

		foreach ($games as $g) {
			$g->tracking_codes = [];
			$gamesSrt[$g->game_type_id] = $g;
		}

		foreach ($boxes as $b) {
			if ($b->tracking_code && (!$b->retired || $retired)) {
				$game = $gamesSrt[$b->game_type_id];
				$game->addTrackingCode($b);
			}
		}
		$this->template['retireAction'] = 'retireBox';
		$this->template['insertAction'] = 'insertBox';
		$this->template['games'] = $gamesSrt;
	}

	public function renderOvladaciPanel() {
		$this->addCss('input-specific.css');
		$this->addJs('input-specific.js');

		$this->template['xml_inventory'] = ['controller' => 'xml', 'action' => 'inventory'];
		$this->template['xml_reservations'] = ['controller' => 'xml', 'action' => 'reservations'];
		$this->template['operator_enabled'] = \config\Config::ENABLE_OPERATOR;
		$this->template['game_types'] = $this->prepareOperatiorGameTypes();
		$this->template['letiste_links'] = $this->makeLetisteLinks();
	}

	private function prepareOperatiorGameTypes() {
		return [
			'import' => ['controller' => 'sprava', 'action' => 'importHer'],
			'export' => ['controller' => 'sprava', 'action' => 'exportHer'],
		];
	}

	private function makeLetisteLinks() {
		$return = [];
		$return[] = ['label' => 'Rezervace', 'url' => ['controller' => 'letiste', 'action' => 'rezervace']];
		return $return;
	}

	public function doExportHer() {
		$gameTypes = Tables\GameType::prepareExport($this->pdo);
		$df = fopen("php://output", 'w');

		\libs\Headders::download_send_headers("CLH_hry_" . date('d_m_Y') . '.csv');

		ob_start();
		$df = fopen("php://output", 'w');
		fputcsv($df, array_keys(reset($gameTypes)));
		foreach ($gameTypes as $gt) {
			fputcsv($df, $gt);
		}
		fclose($df);
		echo ob_get_clean();
		die;
	}

	public function renderHromadnyMail() {
		$this->addCss('input-specific.css');
		$this->addJs('input-specific.js');

		$this->template['pageTitle'] = 'Hromadný mail';

		$this->template['default_subject'] = MailManager::getDefaultSubject();
		$this->template['send_url'] = ['controller' => 'sprava', 'action' => 'poslatMail'];
		$games = array_merge([$this->mockAllUserGameEntry()], Views\GameTypeExtended::fetchAll($this->pdo));
		$this->template['games'] = $games;
		$this->template['active'] = $this->getParam('id');
	}

	private function mockAllUserGameEntry() {
		$allEntry = new Views\GameTypeExtended();
		$allEntry->game_type_id = self::ALL_USERS_GT_ID;
		$allEntry->game_name = 'Všichni uživatelé';
		$allEntry->subscribed_users = Tables\User::count($this->pdo);
		return $allEntry;
	}

	public function doPoslatMail() {
		$gid = $this->getParam('game_type_id', INPUT_POST);
		$subject = $this->getParam('subject', INPUT_POST);
		$body = $this->getParam('mail_body', INPUT_POST);

		$users = ($gid == self::ALL_USERS_GT_ID) ? Views\UserExtended::fetchAll($this->pdo) : Views\Subscription::fetchUsersByGame($this->pdo, $gid);
		
		$result = MailManager::send($users, $body, $subject);
		if ($result['result']) {
			$this->message->success($result['message']);
		} else {
			$this->message->danger($result['message']);
		}

		$this->redirectPars('sprava', $this->getDefaultAction());
	}

}
