<?php

namespace controllers;

use \model\database\tables as Tables,
	\model\database\views as Views;

/**
 * Description of AjaxController
 *
 * @author Stepan
 */
class AjaxController extends Controller {

	const MIN_CODE_LENGTH = 5;

	public function __construct($support) {
		parent::__construct($support);
		$this->layout = "ajax.twig";
	}

	public function doRetireBox() {
		if (!$this->user->isSupervisor()) {
			return 'false';
		}

		$code = $this->getParam("code");
		$box = Tables\GameBox::retire($this->pdo, $code);
		$this->template['response'] = $box ? $code : 'false';
	}

	public function doInsertBox() {
		$code = $this->getParam("code");
		$game_id = $this->getParam("gameId");

		$fail = $this->checkBoxBeforeInsert($code, $game_id);
		if (!$fail) {
			$result = Tables\GameBox::insert($this->pdo, ['game_type_id' => $game_id, 'tracking_code' => $code]);
			if (!$result) {
				$fail = "Při ukládání do databáze nastala neočekávaná chyba";
				;
			}
		}
		$this->template['response'] = (!$fail ? "true" : "$game_id;$fail");
	}

	private function checkBoxBeforeInsert($code, $game_id) {
		if (!$this->user->isSupervisor()) {
			return "Nedostatečná uživatelská oprávnění";
		}
		if (strlen($code) < self::MIN_CODE_LENGTH) {
			return sprintf("Evidenční kód musí být alespoň %d znaků dlouhý.", self::MIN_CODE_LENGTH);
		}
		$gameBox = Views\GameBoxExtended::fetchByCode($this->pdo, $code);
		if ($gameBox) {
			$response = "Kód $code je v databázi již veden, ";
			$response .= ($gameBox->retired ? "je však vyřazený z oběhu" : "náleží hře " . $gameBox->game_name);
			return $response;
		}
		$gameType = GameTypeManager::fetchById($this->pdo, $game_id);
		if (!$gameType) {
			return $this->template['response'] = sprintf("Nebyla nalezena hra %03d", $game_id);
		}
		return false;
	}

	public function doSubscribe() {
		$this->changeSubscribe(true);
	}

	public function doUnsubscribe() {
		$this->changeSubscribe(false);
	}

	private function changeSubscribe($new_value) {
		if (!$this->user->isLoggedIn()) {
			$this->template['response'] = 'false';
			return;
		}
		$game_type_id = $this->getParam("id");
		$user_id = $this->user->user_id;
		Views\Subscription::remove($this->pdo, $user_id, $game_type_id);
		if ($new_value) {
			Views\Subscription::insert($this->pdo, $user_id, $game_type_id);
		}
		$new_sub_count = count(Views\Subscription::fetchUsersByGame($this->pdo, $game_type_id));

		$this->template['response'] = $new_sub_count;
	}

}
