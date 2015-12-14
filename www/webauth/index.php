<?php
session_start();

$user = filter_input(INPUT_SERVER, "WEBAUTH_USER") ? : "kiwi";
if(!$user){
	$error = ['title' => 'Nebyl získán Orion login', 'desc' => 'Od přihlašovacího serveru nebylo možné získat přihlašovací jméno konta orion '
			. 'a přihláśení tím pádem bylo neúspěšné'];
} else {
	$_SESSION['orion_login'] = $user;
	if(isset($_SESSION['login_return_url'])){
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
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
		<!-- Style includes -->
		<link href="../css/bootstrap.css" rel="stylesheet">
		<title>Chyba - CLH</title>
		<style type="text/css">
			#rowbox{ margin-top: 40px; }
		</style>
    </head>
    <body>
		<div class="row" id="rowbox">
            <div class="bodyContainer col-sm-offset-3 col-sm-6">
				<div class="panel panel-danger">
					<div class="panel-heading">Při přihlašování nastala chyba</div>
					<div class="panel-body">
						<h1><?= $error['title'] ?></h1>
						<div class="well well-sm"><?= $error['desc'] ?></div>
						<div class="well">Prosíme vraťte se o stránku zpět a pokuste se přihlásit se znovu. Pokud budou problémy přetrvávat, kontaktuje helpdesk na CIVu.</div>
					</div>
				</div>
            </div>
        </div>
    </body>
</html>
