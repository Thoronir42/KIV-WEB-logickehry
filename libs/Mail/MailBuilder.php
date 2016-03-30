<?php

namespace libs\Mail;

use config\Config;

/**
 * Description of MailSender
 *
 * @author Stepan
 */
class MailBuilder {

	/** @var \Twig_Environment */
	static $twig;
	private static $MAIL_VARS = [ 'appName' => Config::APP_NAME, 'mailMan' => MailSender::FROM_NAME];

	public static function openReservationCreated($users, $pars) {
		self::renderAndSend($users, $pars, 'reservationCreated');
	}

	public static function playerJoinedMyReservation($user, $pars) {
		self::renderAndSend([$user], $pars, 'playerJoinedMyReservation');
	}

	private static function renderAndSend($users, $pars, $template, $subject = null) {
		$body = self::$twig->render("mailTemplates/$template.twig", $pars);
		MailSender::send($users, $body, $subject);
	}

}
