<?php
/**
 * This file is an exemple of PHP file containing good style (according to the default ruleset).
 *
 */
class GoodTest {

	/**
	 * Correctly documented function.
	 *
	 * @param String $a a string 
	 * @param array $b an array
	 * @param Boolean $c a flag
	 * @return String a result
	 */
	function privateFunction($a, array $b = array(), $c = false) { // should have a underscore

		if ($c > $a) {
			return $a + $b;
		} else {
			return $a;
		}
	}

}