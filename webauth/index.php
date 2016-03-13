<?php
session_start();

$user = filter_input(INPUT_SERVER, "WEBAUTH_USER") ? : "kiwi";
if (!$user) {
	$error = ['title' => 'Nebyl získán Orion login', 'desc' => 'Od přihlašovacího serveru nebylo možné získat přihlašovací jméno konta orion '
		. 'a přihláśení tím pádem bylo neúspěšné'];
} else {
	$_SESSION['orion_login'] = $user;
	if (isset($_SESSION['login_return_url'])) {
		$url = $_SESSION['login_return_url'];
		unset($_SESSION['login_return_url']);
		\header("Location: $url");
		\header("Connection: close");
	} else {
		$error = ['title' => 'Chybí návratová adresa', 'desc' => 'Při zpracování požadavku o přihlášení chyběla návratová adresa, pomocí které '
			. 'mělo proběhnout přesměrování zpátky do aplikace.'];
	}
}
?>
<h2><?=$error['title']?></h2>
<p><?=$error['description']?></p>