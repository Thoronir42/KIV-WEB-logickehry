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
	
	/**
	 * 
	 * @param PDOwrapper $pdoConnection
	 * @param Twig_Environment $twig
	 */
    public function __construct($pdoConnection, $twig, $urlGen) {
        $this->pdoWrapper = $pdoConnection;
		$this->twig = $twig;
		$this->urlGen = $urlGen;
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
			case "vypis":	return  new controllers\VypisController();
			case "sprava":	return  new controllers\SpravaController();
			case "rezervace":return new controllers\RezervaceController();
			case "letiste":	return  new controllers\LetisteController();
			case "login":	return  new controllers\LoginController();
			case "xml":		return  new controllers\XMLgenerator();
        }
	}
	private function getControllerInstance($controllerName){
		$cont = self::getControler($controllerName);
		if(!isset($cont->blockSauce)){ $cont->urlGen = $this->urlGen; }
		$cont->pdoWrapper = $this->pdoWrapper;
		return $cont;
    }
	
	public function dispatch($contName, $action = null, $params = null){
		$cont = self::getControllerInstance($contName);
		if($cont == null){
			$this->error(ErrorController::NO_CONTROLLER_FOUND, $contName);
			return;
		}
		$noSauce = isset($cont->blockSauce);
		if(!$noSauce){ $cont->setActiveMenuItem($contName, $action); }
		
		$prepAction = $this->prepareActionName($action);
		$contResponse = $this->getControllerResponse($cont, $prepAction, $noSauce);
		
		$contResponse["startup"]->invoke($cont);
		unset($contResponse["startup"]);
		$this->invokeResponse($contResponse, $cont, $contName, $action, $params);
		
	}
	
	/**
	 * 
	 * @param array $contResponse
	 * @param controllers\Controller $cont
	 * @param string $contName
	 * @param string $action
	 * @param array $params
	 * @return type
	 */
	private function invokeResponse($contResponse, $cont, $contName, $action, $params){
		if(empty($contResponse)){
			$this->error(ErrorController::NOT_RECOGNISED_ACTION, $contName, $action);
			return;
		}
		if(isset($contResponse['do'])){
			$contResponse['do']->invoke($cont, $params);
		}
		if(isset($contResponse['render'])){
			$layoutBody = $this->getLayoutPath($contName, $action);
			if(!$layoutBody){
				$this->error(ErrorController::NO_TEMPLATE, $contName, $action);
				return;
			}
			$contResponse['render']->invoke($cont, $params);
			echo $this->render($layoutBody, $cont->template, $cont->layout);
		} else {
			$this->error(ErrorController::NO_RENDER_OR_REDIRECT, $contName, $action);
		}
	}
	
	private function render($template, $vars, $layout){
		$vars['layout'] = $this->twig->loadTemplate($layout);
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
