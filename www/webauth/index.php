<?php
session_start();
$demo = isset($_SESSION["demo"]);
if ($demo) {
	$pes = filter_input(INPUT_GET, 'peswrt');
	if ($pes == 'TEMMIE') {
		$_SESSION['orion_login'] = 'DEMO';
		if (isset($_SESSION['login_return_url'])) {
			$url = $_SESSION['login_return_url'];
			unset($_SESSION['login_return_url']);
			\header("Location: $url");
			\header("Connection: close");
		} else {
			echo 'bad url';
		}
	}
} else {
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
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<!-- Style includes -->
		<link href="../css/bootstrap.css" rel="stylesheet">

		<script src="../js/jquery-2.1.4.min.js"></script>  
		<script src="../js/bootstrap.js"></script>
		<title><?= $demo ? 'Demo' : 'Chyba' ?> - CLH</title>
		<style type="text/css">
			#rowbox{ margin-top: 40px; }
		</style>
	</head>
	<body>
		<?php if (!$demo) { ?>
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
		<?php } else { ?>
			<div class="row" id="rowbox">
				<div class="bodyContainer col-sm-offset-3 col-sm-6">
					<div class="panel panel-primary">
						<div class="panel-heading">This is it, u lil shit</div>
						<div class="panel-body">
							<?php if ($pes) { ?>
								<div class='alert alert-danger'><b><?= $pes ?></b> is bad wrd. u diddn t pai edukatiin for dis. :( </div>
							<?php } ?>
							<form class="form-horizontal" method="get">
								<label for="peswrt">SHOW ME WHAT U'VE GOT.</label>
								<input type='text' class='form-control' name='peswrt'>
								<div class='row'>
									<div class='col-xs-12'>
										<span class='btn-group '>
											<input type='submit' class='btn btn-default' type='submit' value='I'>
											<input type='submit' class='btn btn-default' type='submit' value='WANNA'>
											<input type='submit' class='btn btn-default' type='submit' value='SEE'>
											<input type='submit' class='btn btn-default' type='submit' value='WHAT'>
											<input type='submit' class='btn btn-default' type='submit' value="YOU'VE">
											<input type='submit' class='btn btn-default' type='submit' value='GOT'>
										</span>
									</div>
								</div>
							</form>
							<br><br><br>
							<a href='#' class='btn btn-default'>
								<span class='glyphicon glyphicon-print'></span> Tisknout
							</a>
							<a href='#' class='btn btn-default'>
								<span class='glyphicon glyphicon-download'></span> Stáhnout
							</a>
							<a href='#' class='btn btn-default'>
								<span class='glyphicon glyphicon-send'></span> Poslat
							</a>
							<div class="dropdown" style='display: inline-block'>
								<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
									<span class='glyphicon glyphicon-bed'></span> Další akce <span class="caret"></span>
								</button>
								<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
									<li><a href="#"><span class='glyphicon glyphicon-duplicate'></span> Duplikovat</a></li>
									<li><a href="#"><span class='glyphicon glyphicon-repeat'></span> Opakovat</a></li>
									<li><a href="#"><span class='glyphicon glyphicon-edit'></span> Upravit</a></li>
									<li role="separator" class="divider"></li>
									<li><a href="#"><span class='glyphicon glyphicon-remove'></span> Smazet</a></li>
								</ul>
							</div>

							<a href='#' class='btn btn-primary'>
								<span class='glyphicon glyphicon-send'></span> Poslat
							</a>
							<div class="dropdown" style='display: inline-block'>
								<button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
									<span class='glyphicon glyphicon-apple'></span> Zadat Platbu <span class="caret"></span>
								</button>
								<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
									<li><a href="#"><span class='glyphicon glyphicon-fire'></span> Duplikovat</a></li>
									<li><a href="#"><span class='glyphicon glyphicon-cog'></span> Bankovním převodem</a></li>
								</ul>
							</div>
						</div>
						<div class="panel-footer">
							OK. BOi
						</div>
					</div>
				</div>
			</div>
		<?php } ?>
	</body>
</html>
