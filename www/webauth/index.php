<?php
session_start();

$user = filter_input(INPUT_SERVER, "WEBAUTH_USER") ? : "kiwi";
$_SESSION['orion_login'] = $user;

$url = $_SESSION['login_return_url'];

\header("Location: $url");
\header("Connection: close");