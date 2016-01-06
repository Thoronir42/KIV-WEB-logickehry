<?php

namespace model;

/**
 * Description of MailManager
 *
 * @author Stepan
 */
class MailManager {

	const MAIL_SERVER = "zcu.cz";

	/**
	 * @param String[] $addressees
	 * @param String $content
	 * @return boolean
	 */
	public static function send($addressees, $subject, $content) {
		if (empty($addressees)) {
			return ['result' => false, 'message' => "Pole adresátů bylo prázdné"];
		}
		if (time() % 2 == 0) {
			return ['result' => true, 'message' => sprintf("Mail(%d) byl odeslán následujícím uživatelům: %s", strlen($content), implode($addressees, ", "))];
		} else {
			return ['result' => false, 'message' => sprintf("Mail(%d) byl odeslán následujícím uživatelům: %s", strlen($content), implode($addressees, ", "))];
		}

		return ['result' => false, 'message' => 'Při odesílání hromadného mailu nastala neočekávaná chyba'];
	}

	private function composeMail() {
		
	}

	public static function getDefaultSubject() {
		return "Oznámení aplikace " . \controllers\Controller::APP_NAME;
	}

}
