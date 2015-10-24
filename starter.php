<?php
include __DIR__.'/libs/autoloader.php';

$cfg = __DIR__.((true) ? "/config/database.local.php" : "/config/database.php");
PDOwrapper::connect(include $cfg);
unset($cfg);

$protocol = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']))
        ? "https" : "http";
$domain = $protocol."://".$_SERVER['SERVER_NAME'];


$title = "CLH";
$menu = [
    ["url" => $domain."/?controller=vypis", "label" => "Výpis týdne", "active" => true],
    ["url" => $domain."/?controller=vypis", "label" => "Správa her"],
    ["url" => $domain."/?controller=letiste", "label" => "(Letiště)"],
    ["url" => $domain."/?controller=xml", "label" => "(XML)"],
];
include 'templates/default.tpl.php';
echo $domain;