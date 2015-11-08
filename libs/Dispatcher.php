<?php
define('DISP_NO_CONTROLLER', 1);
define('DISP_NO_ACTION', 2);
define('DISP_NO_TEMPLATE', 3);
define('DISP_NO_RENDER_OR_REDIRECT', 4);
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
                $cont = new controllers\ErrorController(); break;
			case "vypis":
				$cont = new controllers\VypisController(); break;
            case "rezervace":
                $cont = new controllers\HomeController(); break;
            case "login":
                $cont = new controllers\LoginController(); break;
        }
		$cont->urlGen = $this->urlGen;
		$cont->pdoWrapper = $this->pdoWrapper;
		return $cont;
    }
	
	public function dispatch($contName, $action = null, $params = null){
		$cont = self::getControler($contName);
		
		if($cont instanceof \controllers\ErrorController){
			$this->error(DISP_NO_CONTROLLER, $contName);
			return;
		}
		
		$cont->setActiveMenuItem($contName, $action);
		
		$prepAction = $this->prepareActionName($action);
		$contResponse = $this->getControllerResponse($cont, $prepAction);
		
		$contResponse["startup"]->invoke($cont);
		unset($contResponse["startup"]);
		
		if(empty($contResponse)){
			$this->error(DISP_NO_ACTION, $contName, $action);
			return;
		}
		if(isset($contResponse['do'])){
			$contResponse['do']->invoke($cont, $params);
		}
		if(isset($contResponse['render'])){
			$layoutBody = $this->getLayoutPath($contName, $action);
			if(!$layoutBody){
				$this->error(DISP_NO_TEMPLATE, $contName, $action);
				return;
			}
			$contResponse['render']->invoke($cont, $params);
			echo $this->render($layoutBody, $cont->template, $cont->layout);
		} else {
			$this->error(DISP_NO_RENDER_OR_REDIRECT, $contName, $action);
		}
		
	}
	
	private function render($template, $vars, $layout){
		$vars['layout'] = $this->twig->loadTemplate($layout);
		echo $this->twig->render($template, $vars);
	}
	
	private function error($type, $contName, $action = null){
		$errCont = $this->getControler(null);
		$errCont->startUp();
		switch($type){
			case DISP_NO_CONTROLLER:
				$errCont->renderNoControllerFound($contName);
				break;
			case DISP_NO_ACTION:
				$errCont->renderNotRecognizedAction($contName, $action);
				break;
			case DISP_NO_TEMPLATE:
				$errCont->renderNoTemplate($contName, $action);
				break;
			case DISP_NO_RENDER_OR_REDIRECT:
				$errCont->renderNoRenderFound($contName, $action);
				break;
		}
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
		$return = ["startup" => $contClass->getMethod("startUp")];
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
		if(file_exists("$dir/$controller/$action.twig")){
			$return = "$controller/$action.twig";
			return $return;
		}
		return false;
	}
}
