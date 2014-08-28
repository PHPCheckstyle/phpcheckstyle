<?php
namespace PHPCheckstyle\Config;

/**
 * Loads the test configuration.
 *
 * @author James Brooks <jbrooksuk@me.com>
 */
class CheckArrayStyleConfig extends CheckStyleConfig {
	
	// Array that contains the loaded checks configuration
	public $myConfig = array();

	/**
	 * Constructor.
	 *
	 * @param String $configArray
	 *        	The path of the config file
	 */
	public function __construct($configArray) {
		
		// If the path is a valid file we use it as is
		if (is_array($configArray)) {
			$this->myConfig = $configArray;
		} else {
			echo "Config must be an array";
			exit(0);
		}
	}

	/**
	 * Return a list of items associed with a test.
	 *
	 * @param String $test
	 *        	name of the test
	 * @return array the list of items for this test.
	 */
	public function getTestItems($test) {
		return isset($this->myConfig[$test]['item']) ? $this->myConfig[$test]['item'] : false;
	}

	/**
	 * Return a list of items associed with a configuration.
	 *
	 * @param String $config
	 *        	name of the config
	 * @return array the list of items for this config.
	 */
	public function getConfigItems($config) {
		if (isset($this->myConfig[$config])) {
			return $this->myConfig[$config];
		} else {
			return array();
		}
	}

	/**
	 * Return a list of exceptionfor a test.
	 *
	 * @param String $test
	 *        	name of the test
	 * @return array the list of exceptions for this test.
	 */
	public function getTestExceptions($test) {
		return isset($this->myConfig[$test]['exception']) ? $this->myConfig[$test]['exception'] : false;
	}

	/**
	 * Return a true if the test exist, false otherwise.
	 *
	 * @param String $test
	 *        	name of the test
	 * @return Boolean true if test exists.
	 */
	public function getTest($test) {
		return (array_key_exists($test, $this->myConfig));
	}

	/**
	 * Return the level of severity of a test.
	 *
	 * @param String $test
	 *        	name of the test
	 * @return the level of severity.
	 */
	public function getTestLevel($test) {
		$ret = WARNING;
		if (array_key_exists($test, $this->myConfig) && array_key_exists('level', $this->myConfig[$test])) {
			$ret = $this->myConfig[$test]['level'];
		}
		
		if ($ret != ERROR && $ret != IGNORE && $ret != INFO && $ret != WARNING) {
			echo "Invalid level for test " . $test . " : " . $ret;
			$ret = WARNING;
		}
		
		return $ret;
	}

	/**
	 * Return the regular expression linked to the test.
	 *
	 * @param String $test
	 *        	name of the test
	 * @return the regular expression.
	 */
	public function getTestRegExp($test) {
		$ret = "";
		if (array_key_exists($test, $this->myConfig) && array_key_exists('regexp', $this->myConfig[$test])) {
			$ret = $this->myConfig[$test]['regexp'];
		}
		
		return $ret;
	}

	/**
	 * Return the list of deprecated method and their replacement.
	 *
	 * @param String $test
	 *        	name of the test
	 * @return the list of depecated values.
	 */
	public function getTestDeprecations($test) {
		$ret = "";
		if (array_key_exists($test, $this->myConfig)) {
			$ret = $this->myConfig[$test];
		}
		
		return $ret;
	}

	/**
	 * Return the list of aliases and their replacement.
	 *
	 * @param String $test
	 *        	name of the test
	 * @return the list of replaced values.
	 */
	public function getTestAliases($test) {
		$ret = "";
		if (array_key_exists($test, $this->myConfig)) {
			$ret = $this->myConfig[$test];
		}
		
		return $ret;
	}

	/**
	 * Return the list of replacements.
	 *
	 * @param String $test
	 *        	name of the test
	 * @return the list of replaced values.
	 */
	public function getTestReplacements($test) {
		$ret = "";
		if (array_key_exists($test, $this->myConfig)) {
			$ret = $this->myConfig[$test];
		}
		
		return $ret;
	}

	/**
	 * Return the value of a property
	 *
	 * @param String $test
	 *        	name of the test
	 * @param String $property
	 *        	name of the property
	 * @return the value.
	 */
	public function getTestProperty($test, $property) {
		if (array_key_exists($test, $this->myConfig) && array_key_exists($property, $this->myConfig[$test])) {
			return $this->myConfig[$test][$property];
		} else {
			return false;
		}
	}
}
