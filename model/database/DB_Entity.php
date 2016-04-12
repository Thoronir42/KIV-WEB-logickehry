<?php

namespace model\database;

use model\services\DB_Service;

/**
 * Description of DbEntityModel
 *
 * @author Stepan
 */
abstract class DB_Entity {

	/**
	 * 
	 * @param String $class required DB_Entity class to be instantiated
	 * @return DB_Entity
	 */
	public static function fromPOST(){
		return null;
	}
	
	protected static function createFromPost($class){
		return DB_Service::fromPOST($class);
	}
	

	public static function getExportColumns() {
		return [];
	}

	var $misc = false;

	public function __construct() {
		$this->misc = ['missing' => []];
	}

	public function __isset($name) {
		return array_key_exists($name, $this->misc);
	}

	public function __get($name) {
		if (isset($this->misc[$name])) {
			return $this->misc[$name];
		}
	}

	public function __set($name, $value) {
		$this->misc[$name] = $value;
	}

	public function asArray($includeMissing = false) {
		$ret = [];
		foreach ($this as $prpName => $prpVal) {
			if ($prpName == 'misc' || $prpName == 'message_buffer') {
				continue;
			}
			if(empty($this->$prpName)){
				continue;
			}
			
			$ret[$prpName] = $prpVal;
		}
		if ($includeMissing && isset($this->missing)) {
			$ret['missing'] = $this->missing;
		}

		return $ret;
	}

	/**
	 * 
	 */
	public function readyForInsert() {
		if (empty($this->missing)) {
			return true;
		}
		return $this->checkRequiredProperties(null);
	}

	protected function checkRequiredProperties($class) {
		if ($class == null) {
			return false;
		}
		$rc = new \ReflectionClass($class);

		$properties = $rc->getProperties();
		$instance = $rc->newInstanceArgs();
		foreach ($properties as $prp) {
			$prpName = $prp->name;
			if (isset($this->missing[$prpName]) && $instance->$prpName !== false) {
				echo "$prpName => " . $instance->$prpName;
				return false;
			}
		}
		return true;
	}

	public function getMissingParameters() {
		if (!is_array($this->missing) || empty($this->missing)) {
			return 'wat';
		}
		return implode(", ", array_keys($this->missing));
	}

}
