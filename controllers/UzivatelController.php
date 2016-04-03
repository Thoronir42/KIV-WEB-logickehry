<?php

namespace controllers;

use model\Users,
 libs\ReservationManager;

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
		if (!$user->hasNickname()) {
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
			$action = $this->urlGen->getAction();
			$check = strtolower(substr($action, 0, 7));
			if ($check == 'prihlas') {
				return;
			}
			$this->message->info("Do uživatelské sekce mají přístup jen přihlášení uživatelé.");
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
			$this->message->warning("Uživatel s loginem $orion_login není veden v systému.");
			$this->redirectPars();
		}
		$this->renderProfile($user);
	}

	private function renderProfile($user) {
		$this->addCss('rezervace_vypis.css');
		$this->template['rUser'] = $user;

		$rw = ReservationManager::prepareReservationWeek($this->pdo, 0, $user->user_id);
		$this->template["reservationDays"] = $rw['reservationDays'];
		$this->template['resRend'] = new \model\ReservationRenderer(Tables\Reservation::EARLY_RESERVATION, Tables\Reservation::LATE_RESERVATION);
		
		$this->template['subscriptions'] = $this->buildSubscriptions($user->user_id);
		$this->template['ratings'] = $this->buildRatings($user->user_id);
		$this->template['games'] = Views\GameTypeExtended::fetchAll($this->pdo);
		
		$this->template['resLink'] = ['controller' => 'rezervace', 'action' => 'vypis'];
		$this->template['gameListLink'] = ['controller' => 'vypis', 'action' => 'hry'];
		
		$this->template['resListColSize'] = $this->colSizeFromGet();
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

	public function doUlozitUdaje() {
		$pars = [
			'orion_login' => $this->user->orion_login,
			'nickname' => trim($this->getParam("nickname", INPUT_POST)),
			'is_student' => !empty($this->getParam("is_student", INPUT_POST))
		];
		if (Tables\User::update($this->pdo, $pars)) {
			$this->message->success("Vaše údaje byly zpracovány a uloženy");
		} else {
			$this->message->danger("Při ukládání vašich údajů nastala chyba.");
		}

		$this->redirectPars("uzivatel", $this->getDefaultAction());
	}

	public function doOdhlasitSe() {
		Users::logout();
		$this->message->info("Vaše odhlášení z aplikace proběhlo úspěšně.");
		$this->message->warning("Pro přihlášení pod jiným účtem se nejdříve odhlašte z orion loginu", ['label' => "Odhlásit se", 'url' => self::PORTAL_LOGOUT_URL]);
		$this->redirectPars();
	}

	public function doPrihlasitSe() {
		$loginUrl = $this->urlGen->loginUrl();
		$_SESSION['login_return_url'] = $this->urlGen->url(['controller' => 'uzivatel', 'action' => 'prihlaseni']);
		$this->redirect($loginUrl);
	}

	public function doPrihlaseni() {
		if (!isset($_SESSION['orion_login'])) {
			$this->message->warning("Neplatny pokus o přihlášení!");
			$this->redirectPars();
		}
		$orion_login = $_SESSION["orion_login"];
		unset($_SESSION['orion_login']);

		$user = Users::login($this->pdo, $orion_login);
		if (!$user) {
			$this->message->danger("Nepodařilo se pro vás vytvořit uživatelský účet");
			$this->redirectPars();
		}
		switch ($user->loginStatus) {
			case Users::LOGIN_NEW:
				$this->message->success("Váš uživatelský účet byl úspěšně vytvořen, vítejte $orion_login");
				break;
			case Users::LOGIN_SUCCESS:
				$this->message->success("Vítejte zpět, $orion_login!");
				break;
		}
		$this->redirectPars();
	}

}
