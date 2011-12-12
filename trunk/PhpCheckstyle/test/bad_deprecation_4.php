<?php
/**
 * This file is an exemple of PHP file containing deprecated code.
 * This file should generate 4 warnings with the default config.
 */

class deprecation {

	/**
	 * Depretacted functions.
	 *
	 * @param String $a
	 * @param String $b
	 */
	function testDeprecation($a, $b) {

		$c = split(",", $a);  // 1 - checkDeprecation
		
		$c .= ereg("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $b)); // 2
		
		session_register("barney"); // 3
		
		$c = mysql_connect('mysql_host', 'mysql_user', 'mysql_password'); // 4
		
		$c = $HTTP_GET_VARS['var']; // 5
	}

}
