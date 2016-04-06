<?php

namespace controllers;

use \model\database\tables as Tables,
	\model\database\views as Views;

use model\services\GameTypes;

/**
 * Description of HomeControler
 *
 * @author Stepan
 */
class VypisController extends Controller {

	/** @var GameTypes */
	private $gameTypes;
	
	public function __construct($support) {
		parent::__construct($support);
		
		$this->gameTypes = new GameTypes($this->pdo);
	}
	
	public static function getDefaultAction() {
		return "hry";
	}

	public function startUp() {
		parent::startUp();
		$this->layout = 'layout.twig';
		$this->template['title'] = "CLH";
	}

	public function renderHry() {
		$this->addCss("hra.css");
		$this->addJs('odber_prepinac.js');
		$this->template['pageTitle'] = "Výpis her";
		$this->template['col_game'] = 4;
		$games = Views\GameTypeExtended::fetchAll($this->pdo);
		$this->user->setSubscribedItems(Views\Subscription::fetchGamesByUser($this->pdo, $this->user->user_id));
		$this->template['games'] = $games;
	}

	public function renderDetailHry() {
		$id = $this->getParam("id");
		$gameType = $this->gameTypes->fetchById($id);
		if (!$gameType) {
			$this->message->warning("Požadovaná hra nebyla nalezena.");
			$this->redirectPars('vypis', 'hry');
		}
		$this->addCss("hra.css");
		$this->addJs('odber_prepinac.js');

		$review = Views\GameRatingExtended::fetchOne($this->pdo, $this->user->user_id, $id);

		// @todo: fetch single subscribed game only
		$this->user->setSubscribedItems(Views\Subscription::fetchGamesByUser($this->pdo, $this->user->user_id));

		$this->template['form_action'] = ['controller' => 'vypis', 'action' => 'hodnotit', 'id' => $id];
		$this->template['g'] = $gameType;
		$this->template['ratings'] = $this->buildRatings($id);
		$this->template['rating'] = ['min' => 1, 'def' => 3, 'max' => 5];
		$this->template['highlight'] = $this->getParam("highlight");
		if ($review) {
			$this->template['rating']['def'] = $review->score;
			$this->template['has_review'] = $review->review;
		}
	}

	private function buildRatings($id) {
		return ['list' => Views\GameRatingExtended::fetchAllByGameType($this->pdo, $id),
			'max_score' => Tables\GameRating::SCORE_MAX];
	}

	public function doHodnotit() {
		$is_edit = $this->getParam('edit', INPUT_POST);
		$pars = ['game_type_id' => $this->getParam('id'),
			"user_id" => $this->user->user_id,
			"score" => Tables\GameRating::validate($this->getParam("rating", INPUT_POST)),
			"review" => $this->getParam("review", INPUT_POST),
		];

		if (Tables\GameRating::delete($this->pdo, $pars['user_id'], $pars['game_type_id']) && (Tables\GameRating::insert($this->pdo, $pars))) {
			$this->message->success($is_edit ? "Vaše hodnocení bylo úspěšně upraveno." : "Hodnocení bylo přidáno.");
		} else {
			$this->message->danger("Při ukládání vašeho hodnocení nastala chyba.");
		}
		$redirect = ['controller' => 'vypis', 'action' => 'detailHry', 'id' => $pars['game_type_id']];
		$this->redirect($this->urlGen->url($redirect));
	}

}
