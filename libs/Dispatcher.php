<?php
use controllers\ErrorController;

/**
 * Description of Dispatcher
 *
 * @author Stepan
 */
class Dispatcher {
	
    /** @var Twig_Environment */
	var $twig;
	
	/** @var PDOwrapper */
	var $pdoWrapper;
	
	/** @var libs\URLgen */
	var $urlGen;
	
	/** @var \libs\MessageBuffer */
	var $messageBuffer;
	
	/**
	 * 
	 * @param PDOwrapper $pdoConnection
	 * @param Twig_Environment $twig
	 * @param \libs\URLgen
	 * @param \libs\MessageBuffer;
	 */
    public function __construct($pdoConnection, $twig, $urlGen, $messageBuffer) {
        $this->pdoWrapper = $pdoConnection;
		$this->twig = $twig;
		$this->urlGen = $urlGen;
		$this->messageBuffer = $messageBuffer;
    }
    
	/**
	 * 
	 * @param String $controllerName
	 * @return \controllers\Controller
	 */
	public static function getControler($controllerName){
        switch($controllerName){
            default:
                return null;
			case "vypis":	return	new controllers\VypisController();
			case "sprava":	return	new controllers\SpravaController();
			case "rezervace":return	new controllers\RezervaceController();
			case "letiste":	return	new controllers\LetisteController();
			case "login":	return	new controllers\LoginController();
			case "xml":		return	new controllers\XMLgenerator();
			case "ajax":	return	new controllers\AjaxController();
			case "uzivatel":return new controllers\UzivatelController();
        }
	}
	private function getControllerInstance($controllerName){
		$cont = self::getControler($controllerName);
		if(!$cont){ return null; }
		
		if(!isset($cont->blockSauce)){ 
			$cont->urlGen = $this->urlGen; 
			$cont->messageBuffer = $this->messageBuffer;
			
		}
		$cont->pdoWrapper = $this->pdoWrapper;
		return $cont;
    }
	
	public function dispatch($contName, $action = null){
		$cont = self::getControllerInstance($contName);
		if($cont == null){ 
			$this->error(ErrorController::NO_CONTROLLER_FOUND, $contName);
			return;
		}
		$noSauce = isset($cont->blockSauce);
		if(!$noSauce){ $cont->setActiveMenuItem($contName, $action); }
		
		$prepAction = $this->prepareActionName($action);
		$contResponse = $this->getControllerResponse($cont, $prepAction, $noSauce);
		
		if($cont instanceof \controllers\AjaxController){
			$this->invokeAjaxResponse($cont, $contResponse['do']); return;
		}
		$this->invokeResponse($contResponse, $cont, $contName, $action);
		
	}
	
	/**
	 * 
	 * @param array $contResponse
	 * @param controllers\Controller $cont
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
			$this->render($layoutBody, $cont->template, $cont->layout);
		} else {
			$this->error(ErrorController::NO_RENDER_OR_REDIRECT, $contName, $action);
		}
	}
	
	/**
	 * 
	 * @param controllers\Controller $cont
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
	
	private function error($errType, $contName, $action = null){
		$errCont = new ErrorController();
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
