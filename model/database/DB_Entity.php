<?php
namespace model\database;

/**
 * Description of DbEntityModel
 *
 * @author Stepan
 */
abstract class DB_Entity{
	
	/**
	 * 
	 * @param String $class required DB_Entity class to be instantiated
	 * @return DB_Entity
	 */
	public static function fromPOST($class = null){
		if($class == null){ return null; }
		$rc = new \ReflectionClass($class);
		
		$properties = $rc->getProperties();
		$instance = $rc->newInstanceArgs();
		$missing = [];
		foreach($properties as $prp){
			$prpName = $prp->name;
			$val = filter_input(INPUT_POST, $prpName);
			if($prpName === "misc"){ continue; }
			if(!$val){ $missing[$prpName] = true; }
			$instance->$prpName = $val; 
		}
		if(!empty($missing)){
			$instance->missing = $missing;
		}
		return $instance;
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
	
	public function asArray($includeMissing = false){
		$ret = [];
		foreach($this as $prpKey => $prpVal){
			if($prpKey === "misc"){ continue; }
			$ret[$prpKey] = $prpVal;
		}
		if($includeMissing && isset($this->missing)){
			$ret['missing'] = $this->missing;
		}
		
		return $ret;
	}
	/**
	 * 
	 */
	public function readyForInsert(){
		$everythingSet = !isset($this->missing);
		return $everythingSet;
	}

	
}
