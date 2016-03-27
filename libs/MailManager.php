<?php

namespace libs;

use \model\database\views\UserExtended;

/**
 * Description of MailManager
 *
 * @author Stepan
 */
class MailManager {
	
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
		$mail->Body = $mail->AltBody = $body;

		if (!$mail->send()) {
			return ['result' => false, 'message' => sprintf("Mail se nepodařilo odeslat: " . $mail->ErrorInfo)];
		}
		return ['result' => true, 'message' => sprintf("Mail byl odeslán následujícím uživatelům: %s", implode($users, ", "))];
	}

	public static function getDefaultSubject() {
		return "Oznámení aplikace " . \config\Config::APP_NAME;
	}

	/**
	 * 
	 * @param UserExtended $u
	 */
	private static function getAddress($u) {
		$mailServer = self::MAIL_SERVER;
		if($u->isStudent()){
			$mailServer = self::MAIL_SERVER_STUDENT_PREFIX.$mailServer;
		}
		return $u->getOrionLogin().'@'.$mailServer;
	}

}
