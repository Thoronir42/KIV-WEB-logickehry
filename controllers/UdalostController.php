<?php

namespace controllers;

use model\DatetimeManager;
use \model\database\tables as Tables,
	\model\database\views as Views;

/**
 * Description of UdalostController
 *
 * @author Stepan
 */
class UdalostController extends Controller {

	public function doPridat() {
		$event = Tables\Event::fromPOST();
		$event->author_user_id = $this->user->user_id;
		
		$resrvations = Views\ReservationExtended::fetchWithinTimespan($this->pdo, DatetimeManager::format(['time_from' => strtotime($event->time_from), 'time_to' => strtotime($event->time_to)], DatetimeManager::DB_FULL));
		$total = count($resrvations);
		if ($total > 0) {
			$this->message(sprintf('V den %s není možné vytvořit událost, vytvoření blokuje %d %s.', date(DatetimeManager::HUMAN_DATE_ONLY, strtotime($event->event_date)), $total, $total >= 5 ? 'rezervací' : 'rezervace'), \libs\MessageBuffer::LVL_WAR);
			$this->redirectPars('rezervace', 'vypis');
		}
		$id = Tables\Event::insert($this->pdo, $event);
		if ($id) {
			$this->message("Událost $event->event_title byla úspěšně vytvořena.", \libs\MessageBuffer::LVL_SUC);
			$this->redirectPars('udalost', 'zobrazit', ['id' => $id]);
		}

		$this->message("Při vytváření události nastaly potíže.", \libs\MessageBuffer::LVL_WAR);
		$this->redirectPars("rezervace", "vypis");
	}

	public function renderZobrazit() {
		$this->addCss("rezervace_detail.css");
		
		$id = $this->getParam('id');
		$event = Tables\Event::fetchById($this->pdo, $id);
		if (!$event) {
			$this->message("Událost č. $id nebyla nalezena");
			$this->redirectPars('rezervace', 'vypis');
		}
		
		$this->template['resRend'] = \model\ReservationRenderer::getInstance();
		$this->template['event'] = $event;
		if($event->hasGameAssigned()){
			$this->template['game'] = Views\GameTypeExtended::fetchById($this->pdo, $event->getGameTypeID());	
		}
		
	}

}
