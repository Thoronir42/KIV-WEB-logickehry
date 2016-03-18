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
		$this->template['links'] = [
			'edit' => ['controller' => 'udalost', 'action' => 'upravit', 'id' => $id],
			'delete' => ['controller' => 'udalost', 'action' => 'smazat', 'id' => $id],
		];
	}
	
	public function renderUpravit(){
		$this->addCss("input-specific.css");
		$this->addJs("input-specific.js");
		
		$this->renderZobrazit();
		
		$event = $this->template['event'];
		
		$this->template['games'] = Tables\Event::addNoGame(Views\GameTypeExtended::fetchAll($this->pdo));
		$this->template['links'] = [
			'back' => ['controller' => 'udalost', 'action' => 'zobrazit', 'id' => $this->getParam('id')],
			'submit' => ['controller' => 'udalost', 'action' => 'ulozit'],
		];
	}
	
	public function doUlozit(){
		$event = Tables\Event::fromPOST();
		$event->author_user_id = $this->user->user_id;
		
		if(!$event->readyForInsert()){
			$this->message('Pole události nebyla vyplněna správně, tato obsahovala chyby: '.implode(", ", array_keys($event->misc['missing'])));
			$this->redirectPars('udalost', 'upravit', ['id' => $event->event_id]);
		}
		
		$values = $event->asArray();
		$id = $event->event_id;
		unset($values['event_id']);
		
		Tables\Event::update($this->pdo, $values, $id);
		$this->redirectPars("udalost", "zobrazit", ['id' => $id]);
	}	

}
