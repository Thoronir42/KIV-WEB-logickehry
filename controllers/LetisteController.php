<?php

namespace controllers;

use \model\database\views as Views,
	\model\database\tables as Tables;
use libs\DatetimeManager,
	libs\ReservationManager;

class LetisteController extends Controller {

	public static function getDefaultAction() {
		return 'rezervace';
	}

	protected function getDefaultColSize() {
		return 12;
	}

	public function __construct($support) {
		parent::__construct($support);
		$this->layout = "letiste.twig";
	}

	public function renderRezervace() {
		$this->addCss('rezervace_vypis.css');
		$week = $this->getParam("tyden");
		if (!is_numeric($week)) {
			$week = 0;
		}
		
		$rw = $this->reservationManager->prepareReservationWeek($week);
		$this->template["reservationDays"] = $rw['reservationDays'];
		$this->template["pageTitle"] = $rw['pageTitle'];
		$this->template["timeSpan"] = DatetimeManager::format($rw['timePars'], DatetimeManager::HUMAN_DATE_ONLY);
		
		$this->template['resListColSize'] = $this->colSizeFromGet();
		$this->template['resRend'] = new \model\ReservationRenderer(Tables\Reservation::EARLY_RESERVATION, Tables\Reservation::LATE_RESERVATION);
		$this->template['games'] = Views\GameTypeExtended::fetchAll($this->pdo);
	}

	public function preRender() {
		
	}

}
