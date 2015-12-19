<?php
namespace controllers;

/**
 * Description of AjaxController
 *
 * @author Stepan
 */
class AjaxController extends Controller{
    
	const MIN_CODE_LENGTH = 5;
	
	
	public function __construct(){
		parent::__construct();
		$this->layout = "ajax.twig";
	}
	
	public function doRetireBox(){
		if(!$this->user->isSupervisor()){ return 'false'; }
		
		$code = $this->getParam("code");
		$box = $this->pdoWrapper->retireBox($code);
		$this->template['response'] = $box ? $code : 'false';
	}
	
	public function doInsertBox(){
		$code = $this->getParam("code");
		$game_id = intval($this->getParam("gameId"));
		
		$fail = $this->checkBoxBeforeInsert($code, $game_id);
		if(!$fail){
			$result = $this->pdoWrapper->insertGameBox(['game_type_id' => $game_id, 'tracking_code' => $code]);
			if(!$result){
				$fail =  "Při ukládání do databáze nastala neočekávaná chyba";;
			}
		}
		$this->template['response'] = (!$fail ? "true" : "$game_id;$fail");
	}
	
	private function checkBoxBeforeInsert($code, $game_id){
		if(!$this->user->isSupervisor()){
			return "Nedostatečná uživatelská oprávnění";
		}
		if(strlen($code) < self::MIN_CODE_LENGTH){
			return sprintf("Evidenční kód musí být alespoň %d znaků dlouhý.", self::MIN_CODE_LENGTH);
		}
		$gameBox = $this->pdoWrapper->gameGameBoxByCode($code);
		if($gameBox){
			$response = "Kód $code je v databázi již veden, ";
			$response .= ($gameBox->retired ? "je však vyřazený z oběhu" : "náleží hře ".$gameBox->game_name);
			return $response;
		}
		$gameType = $this->pdoWrapper->gameTypeById($game_id);
		if(!$gameType){
			return $this->template['response'] = sprintf("Nebyla nalezena hra %03d", $game_id);
		}
		return false;
	}
}
