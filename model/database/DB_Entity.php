<?php
namespace model\database;

/**
 * Description of DbEntityModel
 *
 * @author Stepan
 */
abstract class DB_Entity{
	
	public static function fromPOST($class = null){
		if($class == null){ return null; }
		$rc = new \ReflectionClass($class);
		
		$properties = $rc->getProperties();
		$instance = $rc->newInstanceArgs();
		
		foreach($properties as $prp){
			$prpName = $prp->name;
			$val = filter_input(INPUT_POST, $prpName);
			if($prpName === "misc"){ continue; }
			$instance->$prpName = $val; 
		}
		var_dump($instance);
	}
	var $misc;
	
	public function __construct() {
		$this->misc = [];
	}
	
	public function __isset($name) {
		return array_key_exists($name, $this->misc);
	}
	
	public function __get($name) {
		if(isset($this->misc[$name])){
			return $this->misc[$name];
		}
	}

	public function __set($name, $value) {
		$this->misc[$name] = $value;
	}

	
}
