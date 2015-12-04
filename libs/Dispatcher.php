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
    
	public function getControler($controlerName){
        switch($controlerName){
            default:
                return null;
			case "vypis":
				$cont = new controllers\VypisController(); break;
			case "sprava":
				$cont = new controllers\SpravaController(); break;
            case "rezervace":
                $cont = new controllers\RezervaceController(); break;
			case "letiste":
				$noURL = true;
				$cont = new controllers\LetisteController(); break;
            case "login":
                $cont = new controllers\LoginController(); break;
			case "xml":
				$noURL = true;
				$cont = new controllers\XMLgenerator(); break;
        }
		if(!isset($noURL)){ $cont->urlGen = $this->urlGen; }
		$cont->pdoWrapper = $this->pdoWrapper;
		return $cont;
    }
	
	public function dispatch($contName, $action = null, $params = null){
		$cont = self::getControler($contName);
		if($cont == null){
			$this->error(ErrorController::NO_CONTROLLER_FOUND, $contName);
			return;
		}
		
		$isXML = $cont instanceof \controllers\XMLgenerator;
		
		$prepAction = $this->prepareActionName($action);
		$contResponse = $this->getControllerResponse($cont, $prepAction, $isXML);
		
		if(!$isXML){
			$cont->setActiveMenuItem($contName, $action);
		}
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
			$this->error(ErrorController::NO_RENDER_OR_REDIRECT, "$cont", $action);
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
