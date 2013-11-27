<?php
/**
 * This file is an exemple of PHP file containing deprecated code.
 * This file should generate 5 warnings with the default config.
 */

class Deprecation {

	/**
	 * Depretacted functions.
	 *
	 * @param String $a
	 * @param String $b
	 */
	function testDeprecation($a, $b) {

		$a = split(",", $a);  // 1 - checkDeprecation
		
		$a = ereg("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $b); // 2
		
		session_register("barney"); // 3
		
		$a = mysql_db_query('mysql_database', 'mysql_query'); // 4
		
		$a = $HTTP_GET_VARS['var']; // 5
	}

}
