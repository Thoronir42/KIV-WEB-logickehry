<?php
use controllers\Controller,
	controllers\ErrorController;

/**
 * Description of Dispatcher
 *
 * @author Stepan
 */
class Dispatcher {
	
	var $JS_DIR = __DIR__."/../www/js/";
	var $CSS_DIR = __DIR__."/../www/css/";
	
    /** @var Twig_Environment */
	var $twig;
	
	/** @var PDO */
	var $pdo;
	
	/** @var libs\URLgen */
	var $urlGen;
	
	/** @var \libs\MessageBuffer */
	var $messageBuffer;
	
	/**
	 * 
	 * @param PDO $pdo
	 * @param Twig_Environment $twig
	 * @param \libs\URLgen
	 * @param \libs\MessageBuffer;
	 */
    public function __construct($pdo, $twig, $urlGen, $messageBuffer) {
        $this->pdo = $pdo;
		$this->twig = $twig;
		$this->urlGen = $urlGen;
		$this->messageBuffer = $messageBuffer;
    }
    
	/**
	 * 
	 * @param String $controllerName
	 * @param mixed[] $support
	 * @return Controller
	 */
	public static function getControler($controllerName, $support){
		switch($controllerName){
            default:		return  new controllers\ErrorController($support);
			case "vypis":	return	new controllers\VypisController($support);
			case "sprava":	return	new controllers\SpravaController($support);
			case "rezervace":return	new controllers\RezervaceController($support);
			case "letiste":	return	new controllers\LetisteController($support);
			case "login":	return	new controllers\LoginController($support);
			case "xml":		return	new controllers\XMLgenerator($support);
			case "ajax":	return	new controllers\AjaxController($support);
			case "uzivatel":return new controllers\UzivatelController($support);
        }
	}
	
	private function packSupport(){
		return ['pdo' => $this->pdo, 'urlgen' => $this->urlGen, 'mb' => $this->messageBuffer];
	}
	
	private function getControllerInstance($controllerName){
		$support = $this->packSupport();
		$cont = self::getControler($controllerName, $support);
		if(!$cont){ return null; }
		
		if(!isset($cont->blockSauce)){ 
			$cont->urlGen = $this->urlGen; 
			$cont->mb = $this->messageBuffer;
			
		}
		$cont->pdo = $this->pdo;
		return $cont;
    }
	
	public function dispatch($contName, $action = null){
		$cont = self::getControllerInstance($contName);
		
		if(!$contName || strlen($cont) < 1){
			$cont = $this->getControllerInstance(Controller::DEFAULT_CONTROLLER);
			$cont->redirectPars(Controller::DEFAULT_CONTROLLER, $cont->getDefaultAction());
		}
		if(!$action || strlen($action) < 1){
			$cont->redirectPars($contName, $cont->getDefaultAction());
		}
		
		if($cont == null){ 
			$this->error(ErrorController::NO_CONTROLLER_FOUND, $contName);
			return;
		}
		
		$noSauce = isset($cont->blockSauce);
		if(!$noSauce){ $cont->setActiveMenuItem($contName, $action); }
		
		$prepAction = $this->prepareActionName($action);
		$contResponse = $this->getControllerResponse($cont, $prepAction, $noSauce);
		
		if($cont instanceof \controllers\AjaxController){
			$this->invokeAjaxResponse($cont, $contResponse['do']);
			return;
		}
		$this->invokeResponse($contResponse, $cont, $contName, $action);
		
	}
	
	/**
	 * 
	 * @param array $contResponse
	 * @param Controller $cont
	 * @param string $contName
	 * @param string $action
	 * @return type
	 */
	private function invokeResponse($contResponse, $cont, $contName, $action){
		$contResponse["startup"]->invoke($cont);
		if(sizeof($contResponse) < 3){
			$this->error(ErrorController::NOT_RECOGNISED_ACTION, $contName, $action);
			return;
		}
		if(isset($contResponse['do'])){
			$contResponse['do']->invoke($cont, null);
		}
		if(isset($contResponse['render'])){
			$layoutBody = $this->getLayoutPath($contName, $action);
			if(!$layoutBody){
				$this->error(ErrorController::NO_TEMPLATE, $contName, $action);
				return;
			}
			$contResponse['render']->invoke($cont, null);
			$contResponse['prerender']->invoke($cont, null);
			
			$this->addCssJs($cont, $contName, $action);
			
			$this->render($layoutBody, $cont->template, $cont->layout);
		} else {
			$this->error(ErrorController::NO_RENDER_OR_REDIRECT, $contName, $action);
		}
	}
	
	/**
	 * 
	 * @param Controller $cont
	 * @param ReflectionMethod $action 
	 */
	private function invokeAjaxResponse($cont, $action){
		$action->invoke($cont, null);
		echo $this->twig->render($cont->layout, $cont->template);
	}
	
	private function render($template, $vars, $layout){
		$vars['layout'] = $this->twig->loadTemplate($layout);
		$vars['urlgen'] = $this->urlGen;
		
		echo $this->twig->render($template, $vars);
	}
	
	/**
	 * 
	 * @param Controller $cont
	 * @param String $controller
	 * @param String $action
	 */
	private function addCssJs($cont, $controller, $action){
		$filename = $controller."_$action";
		if(file_exists($this->CSS_DIR."$filename.css")){
			$cont->addCss("$filename.css");
		}
		if(file_exists($this->JS_DIR."$filename.js")){
			$cont->addJs("$filename.js");
		}
	}
	
	private function error($errType, $contName, $action = null){
		$errCont = self::getControler('error', $this->packSupport());
		$errCont->urlGen = $this->urlGen;
		$errCont->startUp();
		$errCont->renderError($errType, $contName, $action);
		$this->render("error/default.twig", $errCont->template, $errCont->layout);
		
	}
	
    /**
     * 
     * @param Controler $cont
     * @param string $action
     */
    private function getControllerResponse($cont, $action){
        $contClass = new ReflectionClass($cont);
		$methodTypes = ["do", "render"];
		$return = [];
		$return["startup"] = $contClass->getMethod("startUp");
		$return["prerender"]=$contClass->getMethod("preRender");
		foreach($methodTypes as $mt){
			$methodName = $mt.$action;
			if ( $contClass->hasMethod($methodName) ){
				$method = $contClass->getMethod($methodName);
				$return[$mt] = $method;
			}
		}
		
		return $return;
    }
	
	
	private function prepareActionName($action){
		if($action == null){
			return "Default";
		}
		$return = strtoupper(substr($action, 0, 1)).strtolower(substr($action, 1));
		return $return;
	}
	
	private function getLayoutPath($controller, $action){
		$dir = __DIR__."/../templates";
		//echo "$dir/$controller/$action.twig";
		if(file_exists("$dir/$controller/$action.twig")){
			$return = "$controller/$action.twig";
			return $return;
		}
		return false;
	}
}
