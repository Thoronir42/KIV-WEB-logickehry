<?php

namespace libs\Mail;

use \model\database\views\UserExtended;
use config\Config;

/**
 * Description of MailManager
 *
 * @author Stepan
 */
class MailSender {

	const MAIL_SERVER = 'zcu.cz';
	const MAIL_SERVER_STUDENT_PREFIX = 'students.';
	const FROM_NAME = 'Deskař';
	const FROM_ADDRESS = 'deskar@logickehry.zcu.cz';

	/**
	 * 
	 * @return \PHPMailer
	 */
	private static function getMailBoner() {
		$mail = new \PHPMailer();
		$mail->setLanguage('cz');
		$mail->CharSet = 'UTF-8';
		$mail->setFrom(self::FROM_ADDRESS, self::FROM_NAME);
		return $mail;
	}

	/**
	 * @param UserExtended[] $users
	 * @param String $body
	 * @return boolean
	 */
	public static function send($users, $body, $subject = null) {
		if (empty($users)) {
			return ['result' => false, 'message' => "Pole adresátů bylo prázdné"];
		}

		$mail = self::getMailBoner();

		foreach ($users as $u) {
			$mail->addAddress(self::getAddress($u));
		}

		$mail->Subject = $subject ? : self::getDefaultSubject();
		$mail->msgHTML($body);
		
		if (!$mail->send()) {
			return ['result' => false, 'message' => sprintf("Mail se nepodařilo odeslat: " . $mail->ErrorInfo)];
		}
		$count = count($users);
		$label = (count($users) < 2) ? 'adresátovi' : 'adresátům';
		return ['result' => true, 'message' => sprintf("Mail byl odeslán %d %s", $count, $label)];
	}

	public static function getDefaultSubject() {
		return "Oznámení aplikace " . Config::APP_NAME;
	}

	/**
	 * 
	 * @param UserExtended $u
	 */
	public static function getAddress($u) {
		$mailServer = self::MAIL_SERVER;
		if ($u->isStudent()) {
			$mailServer = self::MAIL_SERVER_STUDENT_PREFIX . $mailServer;
		}
		return $u->getOrionLogin() . '@' . $mailServer;
	}

}
