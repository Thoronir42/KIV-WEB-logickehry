<?php

namespace operator;

/**
 * Description of Operator
 *
 * @author Stepan
 */
class Operator {

	/**
	 * 
	 * @param type $filename
	 * @param \PDO $pdo
	 * @return string
	 */
	public static function injectSQL($filename, $pdo) {
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
