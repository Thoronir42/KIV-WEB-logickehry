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
                return new controllers\ErrorController($this->urlGen);
			case "vypis":
				return new controllers\VypisController($this->urlGen);
            case "rezervace":
                return new controllers\HomeController($this->urlGen);
            case "login":
                return new controllers\LoginController($this->urlGen);
        }
    }
	
	public function dispatch($contName, $params = null){
		$cont = self::getControler($contName);
		$action = isset($params['action']) ? $params['action'] : null;
		$cont->setActiveMenuItem($contName, $action);
		
		$contResponse = $this->getControllerResponse($cont, $action);
		if(empty($contResponse)){
			
		}
		if(isset($contResponse['do'])){
			$contResponse['do']->invoke($cont, $params);
		}
		if(isset($contResponse['render']) && $layout = $this->getLayoutPath($contName, $action)){
			$contResponse['render']->invoke($cont, $params);
			
			$vars = $this->prepareRenderLayoutVars($cont->template);
			
			echo $this->twig->render($layout, $vars);
		} else {
			echo "No render or redirect on $contName/$action";
		}
		
	}
	
	private function prepareRenderLayoutVars($vars){
		$vars['title'] = "CLH";
		$vars['css'] = $this->urlGen->getCss("default.css");
		$vars["hry"] = $this->pdoWrapper->getGamesWithScores();
		$vars['layout'] = $this->twig->loadTemplate('layout.tpl');
		return $vars;

	}
	
    /**
     * 
     * @param Controler $cont
     * @param type $action
     */
    public function getControllerResponse($cont, $action){
        $contClass = new ReflectionClass($cont);
		$methodTypes = ["do", "render"];
		$prepAction = $this->prepareActionName($action);
		
		$return = [];
		foreach($methodTypes as $mt){
			$method = $mt.$prepAction;
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
		$return = strtoupper(substr($action, 0, 1)).strtolower(substr($action, 1));
		return $return;
	}
	
	private function getLayoutPath($controller, $action = null){
		$dir = __DIR__."/../templates";
		$realAction = ($action != null && file_exists("$dir/$controller/$action.tpl")) ?
				$action : "default";
		if(file_exists("$dir/$controller/$realAction.tpl")){
			$return = "$controller/$realAction.tpl";
			return $return;
		}
		return false;
	}
}
