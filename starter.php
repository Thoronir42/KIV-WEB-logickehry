<?php
include __DIR__.'/libs/autoloader.php';

//	Setup PDO connection
$cfg = __DIR__.((true) ? "/config/database.local.php" : "/config/database.php");
PDOwrapper::connect(include $cfg);
unset($cfg);

// Setup Twig templating
$loader = new Twig_Loader_Filesystem(__DIR__.'/templates/');
$twig = new Twig_Environment($loader, array(
    /*'cache' => __DIR__.'/cache/',*/
));
$twigVars = [];


$controller = filter_input(INPUT_GET, 'controller') ?: "vypis";
$action = filter_input(INPUT_GET, 'action') ?: "vse";


$twigVars['title'] = "CLH";
$view = new model\database\views\GameTypeWithScore();
$cont = Dispatcher::getControler($controller);

$cont->setActiveMenuItem($controller, $action);

$twigVars['menu'] = $cont->menu;
$twigVars['css'] = $cont->urlgen->getCss("default.css");

echo $twig->render('default.tpl', $twigVars);

$gamesWithScores = PDOwrapper::getGamesWithScores();
foreach($gamesWithScores as $id => $game){
	foreach($game as $param => $value){
		echo "$param => $value";
	}
	echo "<br/>";
}