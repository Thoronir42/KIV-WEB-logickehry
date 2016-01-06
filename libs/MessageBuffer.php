<?php

namespace libs;

/**
 * MessageBuffer is used to store and track messages
 *
 * @author Stepan
 */
class MessageBuffer {

	const LVL_PRI = 1;
	const LVL_SUC = 2;
	const LVL_INF = 4;
	const LVL_WAR = 8;
	const LVL_DNG = 16;
	const LVL_ALL = 31;
	const PRT_LVL = 'level';
	const PRT_MSG = 'text';
	const PRT_LNK = 'link';

	/**
	 * Array which holds string equivalents to translate the defined keys
	 * @var array
	 */
	static $MSG_LEVELS = [
		self::LVL_PRI => 'primary',
		self::LVL_SUC => "success",
		self::LVL_INF => 'info',
		self::LVL_WAR => 'warning',
		self::LVL_DNG => 'danger'
	];

	/**
	 * Primary instance which is to be used whenever static methods are used.
	 * @var MessageBuffer
	 */
	private static $instance;

	/**
	 * Factory method that returns either singleton or completely new instance based
	 * on param $newInstance.
	 * 
	 * @param boolean $sessionKey decides if new instance should be created
	 * @return MessageBuffer
	 */
	public static function getInstance($sessionKey = false) {
		if ($sessionKey) {
			return new MessageBuffer($sessionKey);
		}

		if (!isset(self::$instance)) {
			self::$instance = new MessageBuffer("alert_log");
		}
		return self::$instance;
	}

	/**
	 * Passes the parameters on singleton instance to create a new message entry.
	 * 
	 * @param string $message 
	 * @param int $level
	 */
	public static function logMessage($message, $level, $link = null) {
		self::getInstance()->log($message, $level, $link);
	}

	/**
	 * Passes the parameters on singleton instance to fetch logged messages and
	 * returns it's result.
	 * 
	 * @param int $levelFilter
	 * @param boolean $messagesOnly
	 */
	public static function getMessages($levelFilter = self::LVL_ALL) {
		return self::getInstance()->getLog($levelFilter);
	}

	var $session_key;

	/**
	 * Private constructor to disable free creating of instances. To generate instance
	 * use static method getInstance.
	 */
	protected function __construct($session_key) {
		$this->session_key = $session_key;
	}

	/**
	 * Stores provided message and it's level as an array in the $messages property.
	 * Checks wether the provided level is valid and has it's string 
	 * representation and if not, changes it to default value.
	 * 
	 * @param string $message
	 * @param int $level
	 */
	public function log($message, $level = self::LVL_INF, $link = null) {
		if (!$this->validLevel($level)) {
			$level = self::LVL_INF;
		}
		if (!isset($_SESSION[$this->session_key])) {
			$_SESSION[$this->session_key] = [];
		}
		$msg = [self::PRT_MSG => $message, self::PRT_LVL => $level];
		if (is_array($link)) {
			if (isset($link['url']) && isset($link['label'])) {
				$msg[self::PRT_LNK] = $link;
			}
		}
		$_SESSION[$this->session_key][] = $msg;
	}

	/**
	 * Filters messages based on provided $levelFilter and possibly fetches
	 * just their text part, leaving their level behind, for easier array
	 * accessing.
	 * 
	 * @param int $levelFilter Bit flag that decides for each message level if
	 * it is to be included in final array. By default all types are included.
	 * 
	 * @param boolean $messagesOnly If set to true, this method will trim the level
	 * part and final array will contain only messages. Otherwise each message
	 * will be in following format:
	 * [MSG_TEXT => "Sample text", MSG_LEVEL => MSG_LVL_###]
	 * 
	 * @return array
	 */
	public function getLog($levelFilter = self::LVL_ALL) {
		if (!isset($_SESSION[$this->session_key])) {
			return null;
		}
		if ($levelFilter == self::LVL_ALL) {
			$filteredMessages = $_SESSION[$this->session_key];
		} else {
			$filteredMessages = [];
			foreach ($_SESSION[$this->session_key] as $messageEntry) {
				if ($messageEntry[self::PRT_LVL] & $levelFilter) {
					$filteredMessages[] = $messageEntry;
				}
			}
		}
		unset($_SESSION[$this->session_key]);
		return $this->numToLvl($filteredMessages);
	}

	private function numToLvl($messages) {
		foreach ($messages as $key => $msg) {
			$messages[$key][self::PRT_LVL] = self::$MSG_LEVELS[$msg[self::PRT_LVL]];
		}
		return $messages;
	}

	/**
	 * Checks if provided message level has it's string representation.
	 * 
	 * @param int $level
	 * 
	 * @return boolean
	 */
	private function validLevel($level) {
		return array_key_exists($level, self::$MSG_LEVELS);
	}

}
