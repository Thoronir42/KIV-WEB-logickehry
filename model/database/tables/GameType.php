<?php

namespace model\database\tables;

/**
 * Description of GameType
 *
 * @author Stepan
 */
class GameType extends \model\database\DB_Entity {

	public static function getExportColumns() {
		return ['game_name', 'game_subtitle', 'avg_playtime', 'min_players', 'max_players'];
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @return GameType[]
	 */
	public static function fetchAll($pdo) {
		return $pdo->query("SELECT * FROM `game_type`")
						->fetchAll(\PDO::FETCH_CLASS, self::class);
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param mixed[] $pars
	 */
	public static function insert($pdo, $pars) {
		$statement = $pdo->prepare("INSERT INTO `web_logickehry_db`.`game_type` "
				. "(`game_type_id`, `game_name`, `game_subtitle`, `avg_playtime`, `max_players`, `min_players`) "
				. "VALUES ( :game_type_id,  :game_name,  :game_subtitle,  :avg_playtime,  :max_players,  :min_players )");
		if ($statement->execute($pars)) {
			return true;
		}
		var_dump($pars);
		echo '<br/>';
		var_dump($statement->queryString);
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param mixed[] $pars
	 * @return boolean
	 */
	public static function update($pdo, $pars) {
		$statement = $pdo->prepare("UPDATE `web_logickehry_db`.`game_type` SET "
				. "`game_name` = :game_name, "
				. "`game_subtitle` = :game_subtitle, "
				. "`avg_playtime` = :avg_playtime, "
				. "`max_players` = :max_players, "
				. "`min_players` = :min_players "
				. "WHERE `game_type_id` = :game_type_id ");
		if ($statement->execute($pars)) {
			return true;
		}
		var_dump($pars);
		echo '<br/>';
		var_dump($statement->queryString);
	}

	public static function prepareExport($pdo) {
		$columns = self::getExportColumns();
		$games = self::fetchAll($pdo);

		$gamesArray = [];
		foreach($games as $g) {
			$ga = [];
			foreach($columns as $c){
				$ga[$c] = $g->$c;
			}
			$gamesArray[] = $ga;
		}
		return $gamesArray;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @return type
	 */
	public static function nextId($pdo) {
		$result = $pdo->query("SELECT game_type_id FROM game_type "
						. "ORDER BY game_type_id DESC")->fetchColumn();
		return $result + 1;
	}

	/**
	 * 
	 * @return GameType
	 */
	public static function fromPOST() {
		$gt = parent::fromPOST(self::class);
		if (empty($gt->max_players)) {
			$gt->max_players = $gt->min_players;
		}
		return $gt;
	}

	var $game_type_id = false;
	var $game_name;
	var $game_subtitle = false;
	var $avg_playtime;
	var $min_players;
	var $max_players = false;

	protected function checkRequiredProperties() {
		return parent::checkRequiredProperties(self::class);
	}

	public function getColor() {
		return \model\ColorManager::numberToColor($this->game_type_id);
	}

	public function getFullName() {
		$return = $this->game_name;
		if (!empty($this->game_subtitle)) {
			$return .= ' ' . $this->game_subtitle;
		}
		return $return;
	}

	public function getPlayerCount($separator = ' - ') {
		$reverse = ($this->min_players > $this->max_players);
		$less = $reverse ? $this->max_players : $this->min_players;
		$more = $reverse ? $this->min_players : $this->max_players;
		if ($less < 1 || $less == $more) {
			return $more;
		}
		return "$less$separator$more";
	}

}
