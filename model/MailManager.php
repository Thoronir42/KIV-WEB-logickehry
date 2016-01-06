<?php

namespace model;

/**
 * Description of MailManager
 *
 * @author Stepan
 */
class MailManager {

	const MAIL_SERVER = "zcu.cz";
	const FROM_NAME = "Deskař";
	const FROM_ADDRESS = "deskar@logickehry.zcu.cz";

	/**
	 * 
	 * @return \PHPMailer
	 */
	private static function getMailBoner() {
		$mail = new \PHPMailer();
		$mail->setLanguage('cz');
		$mail->setFrom(self::FROM_ADDRESS, self::FROM_NAME);
		return $mail;
	}

	/**
	 * @param String[] $users
	 * @param String $body
	 * @return boolean
	 */
	public static function send($users, $body, $subject = null) {
		if (empty($users)) {
			return ['result' => false, 'message' => "Pole adresátů bylo prázdné"];
		}

		$mail = self::getMailBoner();

		foreach ($users as $u) {
			$mail->addAddress("$u@" . self::MAIL_SERVER);
		}

		$mail->Subject = $subject ? : self::getDefaultSubject();
		$mail->Body = $mail->AltBody = $body;

		if (!$mail->send()) {
			return ['result' => false, 'message' => sprintf("Mail se nepodařilo odeslat: " . $mail->ErrorInfo)];
		}
		return ['result' => true, 'message' => sprintf("Mail byl odeslán následujícím uživatelům: %s", implode($users, ", "))];
	}

	public static function getDefaultSubject() {
		return "Oznámení aplikace " . \controllers\Controller::APP_NAME;
	}

}
