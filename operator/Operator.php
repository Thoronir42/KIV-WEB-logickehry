<?php

namespace operator;

/**
 * Description of Operator
 *
 * @author Stepan
 */
class Operator {

	const DEFAULT_SQL_FILE = 'db_logickehry';
	
	/**
	 * 
	 * @param \PDO $pdo
	 * @param String $filename
	 * @return string
	 */
	public static function injectSQL($pdo, $filename = null) {
		if(!$filename){
			$filename = self::DEFAULT_SQL_FILE;
		}
		if (!file_exists($filename)) {
			return 'Soubor SQL injekce nebyl nalezen';
		}
		$statement = file_get_contents($filename);
		if(!$pdo->exec($statement)){
			return 'Při vykonávání SQL souboru nastala chyba #'.$pdo->errorCode();
		}
		return false;
	}

}
