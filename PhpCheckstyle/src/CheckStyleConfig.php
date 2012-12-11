<?php
/*
 *  $Id: CheckStyleConfig.php 28215 2005-07-28 02:53:05Z hkodungallur $
*
*  Copyright(c) 2004-2005, SpikeSource Inc. All Rights Reserved.
*  Licensed under the Open Source License version 2.1
*  (See http://www.spikesource.com/license.html)
*/

if (!defined("PHPCHECKSTYLE_HOME_DIR")) {
	define("PHPCHECKSTYLE_HOME_DIR", dirname(__FILE__)."/..");
}

/**
 * Loads the user specified test configuration
 *
 * @author Hari Kodungallur <hkodungallur@spikesource.com>
 * @version $Revision: $
 */
class CheckStyleConfig {
	private $file;

	// Array that contains the loaded checks
	public $_myConfig = array();

	private $_currentTest = false;

	private $_currentConfig = false;

	private $_xmlParser;

	/**
	 * constructor
	 *
	 * @access public
	 */
	public function CheckStyleConfig() {

		$_isAbsolutePath = preg_match("/^[a-zA-Z]{1}:\\.*/", CONFIG_FILE);

		if ($_isAbsolutePath) {
			$this->file = CONFIG_FILE;
		} else {
			$this->file = PHPCHECKSTYLE_HOME_DIR."/config/".CONFIG_FILE;
		}

		$this->_xmlParser = xml_parser_create();
		xml_set_object($this->_xmlParser, $this);
		xml_set_element_handler($this->_xmlParser, "_startElement", "_endElement");
		xml_set_character_data_handler($this->_xmlParser, "_gotCdata");
		xml_set_default_handler($this->_xmlParser, "_gotCdata");
	}

	/**
	 * destructor
	 *
	 * @return
	 * @access public
	 */
	public function __destruct() {
		xml_parser_free($this->_xmlParser);
	}

	/**
	 * parses the configuration file and stores the values
	 *
	 * @return none
	 * @access public
	 */
	public function parse() {
		// example from php.net
		$fp = fopen($this->file, "r");
		if (!$fp) {
			die("Could not open XML input file");
		}

		$data = fread($fp, 4096);
		while ($data) {
			if (!xml_parse($this->_xmlParser, $data, feof($fp))) {
				$msg = sprintf("Warning: XML error: %s at line %d", xml_error_string(xml_get_error_code($this->_xmlParser)), xml_get_current_line_number($this->_xmlParser));
				echo $msg;
				$this->_myConfig = array();
			}

			$data = fread($fp, 4096);
		}
	}

	/**
	 * Return a list of items associed with a test.
	 *
	 * @param String $test name of the test
	 * @return array the list of items for this test.
	 */
	public function getTestItems($test) {
		$test = strtolower($test);
		return isset($this->_myConfig[$test]['item']) ? $this->_myConfig[$test]['item'] : false;
	}

	/**
	 * Return a list of items associed with a configuration.
	 *
	 * @param String $config name of the config
	 * @return array the list of items for this config.
	 */
	public function getConfigItems($config) {
		return $this->_myConfig[strtolower($config)];
	}

	/**
	 * Return a list of exceptionfor a test.
	 *
	 * @param String $test name of the test
	 * @return array the list of exceptions for this test.
	 */
	public function getTestExceptions($test) {
		$test = strtolower($test);
		return isset($this->_myConfig[$test]['exception']) ? $this->_myConfig[$test]['exception'] : false;
	}

	/**
	 * Return a true if the test exist, false otherwise.
	 *
	 * @param String $test name of the test
	 * @return Boolean true if test exists.
	 */
	public function getTest($test) {
		$test = strtolower($test);
		return (array_key_exists($test, $this->_myConfig));
	}

	/**
	 * Return the level of severity of a test.
	 *
	 * @param String $test name of the test
	 * @return the level of severity.
	 */
	public function getTestLevel($test) {
		$test = strtolower($test);
		$ret = WARNING;
		if (array_key_exists($test, $this->_myConfig) && array_key_exists('level', $this->_myConfig[$test])) {
			$ret = $this->_myConfig[$test]['level'];
		}

		if ($ret != ERROR && $ret != IGNORE && $ret != INFO && $ret != WARNING) {
			echo "Invalid level for test ".$test." : ".$ret;
			$ret = WARNING;
		}

		return $ret;
	}

	/**
	 * Return the regular expression linked to the test.
	 *
	 * @param String $test name of the test
	 * @return the regular expression.
	 */
	public function getTestRegExp($test) {
		$test = strtolower($test);
		$ret = "";
		if (array_key_exists($test, $this->_myConfig) && array_key_exists('regexp', $this->_myConfig[$test])) {
			$ret = $this->_myConfig[$test]['regexp'];
		}

		return $ret;
	}

	/**
	 * Return the list of deprecated method and their replacement.
	 *
	 * @param String $test name of the test
	 * @return the list of depecated values.
	 */
	public function getTestDeprecations($test) {
		$test = strtolower($test);
		$ret = "";
		if (array_key_exists($test, $this->_myConfig)) {
			$ret = $this->_myConfig[$test];
		}

		return $ret;
	}
	
	/**
	 * Return the list of aliases and their replacement.
	 *
	 * @param String $test name of the test
	 * @return the list of depecated values.
	 */
	public function getTestAliases($test) {
		$test = strtolower($test);
		$ret = "";
		if (array_key_exists($test, $this->_myConfig)) {
			$ret = $this->_myConfig[$test];
		}
	
		return $ret;
	}

	/**
	 * Return the value of a property
	 *
	 * @param String $test name of the test
	 * @param String $property name of the property
	 * @return the value.
	 */
	public function getTestProperty($test, $property) {
		$test = strtolower($test);
		$property = strtolower($property);
		if (array_key_exists($test, $this->_myConfig) && array_key_exists($property, $this->_myConfig[$test])) {
			return $this->_myConfig[$test][$property];
		} else {
			return false;
		}
	}

	/**
	 * SAX function indicating start of an element
	 * Store the TEST and PROPERTY values in an array
	 *
	 * @param Parser $parser the parser
	 * @param Elem $elem name of element
	 * @param Attributes $attrs list of attributes of the element
	 */
	private function _startElement($parser, $elem, $attrs) {
		switch ($elem) {

			// Case of a configuration property
			case 'CONFIG':
				$this->_currentConfig = strtolower($attrs['NAME']);
				$this->_myConfig[$this->_currentConfig] = array();
				break;

				// Case of a configuration property item
			case 'CONFIGITEM':
				$this->_myConfig[$this->_currentConfig][] = $attrs['VALUE'];
				break;

				// Case of a test rule
			case 'TEST':
				$this->_currentTest = strtolower($attrs['NAME']);
				$this->_myConfig[$this->_currentTest] = array();

				if (isset($attrs['LEVEL'])) {
					$this->_myConfig[$this->_currentTest]['level'] = $attrs['LEVEL'];
				}

				if (isset($attrs['REGEXP'])) {
					$this->_myConfig[$this->_currentTest]['regexp'] = $attrs['REGEXP'];
				}
				break;

				// Case of a propertie of a rule (name / value)
			case 'PROPERTY':
				$pname = $attrs['NAME'];
				$pval = true;
				if (array_key_exists('VALUE', $attrs)) {
					$pval = $attrs['VALUE'];
				}
				$this->_myConfig[$this->_currentTest][strtolower($pname)] = $pval;
				break;

				// Case of a item of a list of values of a rule
			case 'ITEM':
				if (isset($attrs['VALUE'])) {
					$this->_myConfig[$this->_currentTest]['item'][] = $attrs['VALUE'];
				}
				break;

				// Case of an exception to a rule
			case 'EXCEPTION':
				if (isset($attrs['VALUE'])) {
					$this->_myConfig[$this->_currentTest]['exception'][] = $attrs['VALUE'];
				}
				break;

				// Case of a deprecated function
			case 'DEPRECATED':
				if (isset($attrs['OLD'])) {
					$this->_myConfig[$this->_currentTest][strtolower($attrs['OLD'])]['old'] = $attrs['OLD'];
				}
				if (isset($attrs['NEW'])) {
					$this->_myConfig[$this->_currentTest][strtolower($attrs['OLD'])]['new'] = $attrs['NEW'];
				}
				if (isset($attrs['VERSION'])) {
					$this->_myConfig[$this->_currentTest][strtolower($attrs['OLD'])]['version'] = $attrs['VERSION'];
				}
				break;

				// Case of an alias function
			case 'ALIAS':
				if (isset($attrs['OLD'])) {
					$this->_myConfig[$this->_currentTest][strtolower($attrs['OLD'])]['old'] = $attrs['OLD'];
				}
				if (isset($attrs['NEW'])) {
					$this->_myConfig[$this->_currentTest][strtolower($attrs['OLD'])]['new'] = $attrs['NEW'];
				}
				break;

			default:
				break;
		}
	}

	/**
	 * SAX function indicating end of element
	 * Currenlty we dont need to do anything here
	 *
	 * @param Parser $parser
	 * @param String $name
	 */
	private function _endElement($parser, $name) {
	}

	/**
	 * SAX function for processing CDATA
	 * Currenlty we dont need to do anything here
	 *
	 * @param Parser $parser
	 * @param String $name
	 */
	private function _gotCdata($parser, $name) {
	}

}
