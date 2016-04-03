<?php

namespace controllers;

use libs\DatetimeManager;
use \model\database\tables as Tables,
	\model\database\views as Views;

use model\services\Reservations;
use model\services\Events;

/**
 * Description of UdalostController
 *
 * @author Stepan
 */
class UdalostController extends Controller {
	
	/** @var Reservations */
	private $reservations;
	
	/** @var Events */
	private $events;
	
	public function __construct($support) {
		parent::__construct($support);
		
		$this->reservations = new Reservations($this->pdo);
		$this->events = new Events($this->pdo);
	}

	
	public function doPridat() {
		$event = Tables\Event::fromPOST();
		$event->author_user_id = $this->user->user_id;
		$dateTime = DatetimeManager::reformat(['time_from' => strtotime($event->time_from), 'time_to' => strtotime($event->time_to)], DatetimeManager::DB_TIME_ONLY);
		$dateTime['date'] = DatetimeManager::reformat($event->event_date, DatetimeManager::DB_DATE_ONLY);
		
		$date_from = $dateTime['date'].' '.$dateTime['time_from'];
		$date_to = $dateTime['date'].' '.$dateTime['time_to'];
		
		
		$resrvations = $this->reservations->fetchWithin($date_from, $date_to);
		$total = count($resrvations);
		if ($total > 0) {
			$this->message->warning(sprintf('V den %s není možné vytvořit událost, vytvoření blokuje %d %s.', date(DatetimeManager::HUMAN_DATE_ONLY, strtotime($event->event_date)), $total, $total >= 5 ? 'rezervací' : 'rezervace'));
			$this->redirectPars('rezervace', 'vypis');
		}
		$id = Tables\Event::insert($this->pdo, $event);
		if ($id) {
			$this->message->success("Událost $event->event_title byla úspěšně vytvořena.");
			$this->redirectPars('udalost', 'zobrazit', ['id' => $id]);
		}

		$this->message->warning("Při vytváření události nastaly potíže.");
		$this->redirectPars("rezervace", "vypis");
	}

	public function renderZobrazit() {
		$this->addCss("rezervace_detail.css");
		
		$id = $this->getParam('id');
		$event = Tables\Event::fetchById($this->pdo, $id);
		if (!$event) {
			$this->message->warning("Událost č. $id nebyla nalezena");
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
			$this->message->warning('Pole události nebyla vyplněna správně, tato obsahovala chyby: '.implode(", ", array_keys($event->misc['missing'])));
			$this->redirectPars('udalost', 'upravit', ['id' => $event->event_id]);
		}
		
		$values = $event->asArray();
		$id = $event->event_id;
		unset($values['event_id']);
		
		if(Tables\Event::update($this->pdo, $values, $id)){
			$this->message->success("Událost byla úspěšně upravena");
		} else {
			$this->message->danger("Při ukládání úprav nastala neočekávaná chyba");
		}
		
		$this->redirectPars("udalost", "zobrazit", ['id' => $id]);
	}
	
	public function doSmazat(){
		$id = $this->getParam('id');
		
		if(Tables\Event::delete($this->pdo, $id)){
			$this->message->info("Událost byla odstraněna");
		}
		$this->redirectPars("rezervace", "vypis");
	}

}
