<?php

namespace controllers;

use model\UserManager;
use \model\database\tables as Tables,
	\model\database\views as Views;

/**
 * Description of UzivatelControler
 *
 * @author Stepan
 */
class UzivatelController extends Controller {

	const PORTAL_LOGOUT_URL = 'https://portal.zcu.cz/portal/logout';

	public static function getDefaultAction() {
		return "mujProfil";
	}

	public static function buildUserActionsMenu($user) {

		$changeDetails = ["urlParams" => ["controller" => "uzivatel", "action" => "mujProfil"],
			"text" => "Můj profil"];
		if(!$user->hasNickname()){
			$changeDetails['label'] = 'label-info';
		}
		$separator = ["separator" => true];
		$logout = ["urlParams" => ["controller" => "uzivatel", "action" => "odhlasitSe"],
			"text" => "Odhlásit se"];
		$menu = [$changeDetails, $separator, $logout];
		return $menu;
	}

	public function startUp() {
		parent::startUp();
		if (!$this->user->isLoggedIn()) {
			$check = strtolower(substr($this->action, 0, 7));
			if ($check == 'prihlas') {
				return;
			}
			$this->message("Do uživatelské sekce mají přístup jen přihlášení uživatelé.");
			$this->redirectPars(Controller::DEFAULT_CONTROLLER);
		}
	}

	public function renderMujProfil() {
		$this->template['form_action'] = ["controller" => "uzivatel", "action" => "ulozitUdaje"];
		$this->addCss("uzivatel_zobrazitProfil.css");
		$this->renderProfile($this->user);
	}

	public function renderZobrazitProfil() {
		$orion_login = $this->getParam('login');
		$user = Views\UserExtended::fetch($this->pdo, $orion_login);
		if (!$user) {
			$this->message("Uživatel s loginem $orion_login není veden.");
			$this->redirectPars();
		}
		$this->renderProfile($user);
	}

	private function renderProfile($user) {
		$this->template['rUser'] = $user;

		$this->template['subscriptions'] = $this->buildSubscriptions($user->user_id);
		$this->template['ratings'] = $this->buildRatings($user->user_id);
		$this->template['reservations'] = $this->buildReservations($user->user_id);

		$this->template['resLink'] = ['controller' => 'rezervace', 'action' => 'vypis'];
		$this->template['gameListLink'] = ['controller' => 'vypis', 'action' => 'hry'];
	}

	private function buildSubscriptions($user_id) {
		$ret = [];
		$ret['list'] = Views\Subscription::fetchGamesDetailedByUser(
						$this->pdo, $user_id);
		$ret['gpr'] = 2;
		return $ret;
	}

	private function buildRatings($user_id) {
		$ret = [];
		$ret['list'] = Views\GameRatingExtended::fetchAllByUser($this->pdo, $user_id);
		$ret['max_score'] = Tables\GameRating::SCORE_MAX;
		return $ret;
	}

	private function buildReservations($user_id) {
		$ret = [];

		return $ret;
	}

	public function doUlozitUdaje() {
		$pars = ["orion_login" => $this->user->orion_login,
			"nickname" => trim($this->getParam("nickname", INPUT_POST)),
		];
		if (Tables\User::update($this->pdo, $pars)) {
			$this->message("Vaše údaje byly zpracovány...", \libs\MessageBuffer::LVL_SUC);
		} else {
			$this->message("Při ukládání vašich údajů nastala chyba.", \libs\MessageBuffer::LVL_DNG);
		}

		$this->redirectPars("uzivatel", $this->getDefaultAction());
	}

	public function doOdhlasitSe() {
		UserManager::logout();
		$this->message("Vaše odhlášení z aplikace proběhlo úspěšně.", \libs\MessageBuffer::LVL_INF);
		$this->message("Pro přihlášení pod jiným účtem se nejdříve odhlašte z orion loginu", \libs\MessageBuffer::LVL_WAR, ['label' => "Odhlásit se", 'url' => self::PORTAL_LOGOUT_URL]);
		$this->redirectPars();
	}

	public function doPrihlasitSe() {
		$loginUrl = $this->urlGen->loginUrl();
		$_SESSION['login_return_url'] = $this->urlGen->url(['controller' => 'uzivatel', 'action' => 'prihlaseni']);
		$this->redirect($loginUrl);
	}

	public function doPrihlaseni() {
		if (!isset($_SESSION['orion_login'])) {
			$this->message("Neplatny pokus o přihlášení!", \libs\MessageBuffer::LVL_WAR);
			$this->redirectPars();
		}
		$orion_login = $_SESSION["orion_login"];
		unset($_SESSION['orion_login']);

		$user = UserManager::login($this->pdo, $orion_login);
		if (!$user) {
			$this->message("Nepodařilo se pro vás vytvořit uživatelský účet", \libs\MessageBuffer::LVL_DNG);
			$this->redirectPars();
		}
		switch ($user->loginStatus) {
			case UserManager::LOGIN_NEW:
				$this->message("Váš uživatelský účet byl úspěšně vytvořen, vítejte $orion_login", \libs\MessageBuffer::LVL_SUC);
				break;
			case UserManager::LOGIN_SUCCESS:
				$this->message("Vítejte zpět, $orion_login!", \libs\MessageBuffer::LVL_SUC);
				break;
		}
		$this->redirectPars();
	}

}
