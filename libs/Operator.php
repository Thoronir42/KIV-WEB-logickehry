<?php

namespace libs;

/**
 * Description of Operator
 *
 * @author Stepan
 */
class Operator {

	const DEFAULT_SQL_FILE = 'db_logickehry';
	const SQL_FOLDER = '/../operator/';

	/**
	 * 
	 * @param \PDO $pdo
	 * @param String $filename
	 * @return string
	 */
	public static function injectSQL($pdo, $filename = null) {
		if (!$filename) {
			$filename = self::DEFAULT_SQL_FILE;
		}
		$path = self::SQL_FOLDER . $filename . ".sql";
		if (!file_exists($path)) {
			return 'Soubor SQL injekce nebyl nalezen';
		}
		$statement = file_get_contents($path);
		if (!$pdo->exec($statement)) {
			return 'Při vykonávání SQL souboru nastala chyba #' . $pdo->errorCode();
		}
		return false;
	}

}
