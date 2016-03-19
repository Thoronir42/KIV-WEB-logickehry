<?php

namespace model\database;

use libs\MessageBuffer;

/**
 * Description of DbEntityModel
 *
 * @author Stepan
 */
abstract class DB_Entity {

	/**
	 * @var MessageBuffer
	 */
	public static $message_buffer;

	public static function logError($errorInfo, $function, $sql = null) {
		if (!\config\Config::LOG_DB_ERRORS) {
			return;
		}
		$message = sprintf("DB error <strong>%s</strong> in function <strong>%s</strong>", $errorInfo[2], $function);
		if (!is_null($sql)) {
			$message .= "<br/>SQL: " . $sql;
		}
		self::$message_buffer->log($message, MessageBuffer::LVL_DNG);
	}

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
			if ($prpName == 'misc' || $prpName == 'message_buffer') {
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
		foreach ($this as $prpName => $prpVal) {
			if ($prpName == 'misc' || $prpName == 'message_buffer') {
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
