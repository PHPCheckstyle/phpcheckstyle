<?php

/**
 * This file is an exemple of PHP file containing bad naming.
 * This test file should generate 6 warnings with the default config.
 */



// 1 : constant naming :: PHPCHECKSTYLE_CONSTANT_NAMING
define(_badly_named_constant, "A_CONSTANT_VALUE");

// 2 : constant naming :: PHPCHECKSTYLE_CONSTANT_NAMING
const bad_CONST = "goo";


// 3 : top level var naming :: PHPCHECKSTYLE_TOPLEVEL_VARIABLE_NAMING
$X = 1;

//
/**
 * 4 : class naming :: PHPCHECKSTYLE_CLASSNAME_NAMING
 * 
 * @SuppressWarnings checkUnusedVariables
 */
class 9badlynamedclass {
	
	//5 : member level var naming :: PHPCHECKSTYLE_MEMBER_VARIABLE_NAMING 
	$Y = 1;
	
	/**
	 * 6 :constructor Naming :: Should be old style
	 */
	function __construct() {		
	}
	

	/**
	 * 7 : function naming :: PHPCHECKSTYLE_FUNCNAME_NAMING
	 */
	function Badlynamedfunction() {
		
		//8 : local level var naming :: PHPCHECKSTYLE_LOCAL_VARIABLE_NAMING
		$Z = 1;

	}

	/**
	 * 9 : protected function naming :: PHPCHECKSTYLE_PROTECTED_FUNCNAME_NAMING
	 */
	protected function Badlynamedfunction2() {
		badlynamedfunction3();
	}

	/**
	 * 10 : private function naming :: PHPCHECKSTYLE_PRIVATE_FUNCNAME_NAMING
	 */
	private function badlynamedfunction3() {

	}
	
}


/**
* 11 : interface naming
*/
interface _badlynamedinterface {
}


// 12 File Naming