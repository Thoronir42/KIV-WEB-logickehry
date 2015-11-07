<?php

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
    
	public function getControler($controlerName = "Rezervace"){
        switch($controlerName){
            default: 
                return new controllers\ErrorController();
			case "vypis":
				return new controllers\VypisController();
            case "rezervace":
                return new controllers\HomeController();
            case "login":
                return new controllers\LoginController();
        }
    }
	
	public function dispatch($contName, $action = null, $params = null){
		$cont = self::getControler($contName);
		$cont->urlGen = $this->urlGen;
		$cont->pdoWrapper = $this->pdoWrapper;
		
		$cont->setActiveMenuItem($contName, $action);
		
		$prepAction = $this->prepareActionName($action);
		$contResponse = $this->getControllerResponse($cont, $prepAction);
		
		$contResponse["startup"]->invoke($cont);
		
		if(empty($contResponse)){
			echo "Action $prepAction was could not be executed nor rendered.";
			return;
		}
		if(isset($contResponse['do'])){
			$contResponse['do']->invoke($cont, $params);
		}
		if(isset($contResponse['render'])){
			$layoutBody = $this->getLayoutPath($contName, $action);
			
			$contResponse['render']->invoke($cont, $params);
			$twigVars = $cont->template;
			$twigVars['layout'] = $this->twig->loadTemplate($cont->layout);
			
			echo $this->twig->render($layoutBody, $twigVars);
		} else {
			echo "No render or redirect on $cont/$action";
		}
		
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
			$method = $mt.$action;
			if ( $contClass->hasMethod($method) ){
				$m = $contClass->getMethod($method);
				$return[$mt] = $m;
			}
		}
		
		if(!isset($return['render'])){
			if($contClass->hasMethod("renderDefault")){
				$return['render'] = $contClass->getMethod("renderDefault");
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
