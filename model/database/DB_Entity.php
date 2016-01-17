<?php

namespace model\database;

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
	public static function fromPOST($class = null) {
		if ($class == null) {
			return null;
		}
		$rc = new \ReflectionClass($class);

		$properties = $rc->getProperties();
		$instance = $rc->newInstanceArgs();
		$missing = [];
		foreach ($properties as $prp) {
			$prpName = $prp->name;
			if ($prpName == 'misc') {
				continue;
			}
			$val = \filter_input(INPUT_POST, $prpName);
			if (!empty($val) && $val !== "0") {
				$instance->$prpName = $val;
			} else if ($instance->$prpName !== false) {
				$missing[$prpName] = true;
			}
		}
		if (!empty($missing)) {
			$instance->missing = $missing;
		}
		return $instance;
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
		foreach ($this as $prpKey => $prpVal) {
			if ($prpKey === "misc" || is_null($this->$prpKey)) {
				continue;
			}
			$ret[$prpKey] = $prpVal;
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
		if (!isset($this->missing)) {
			return true;
		}
		return $this->checkRequiredProperties();
	}

	protected function checkRequiredProperties($class = null) {
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
