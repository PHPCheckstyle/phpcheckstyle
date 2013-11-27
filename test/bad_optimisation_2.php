<?php
/**
 * This file is an exemple of PHP file containing unoptimized code.
 * This file should generate 2 warnings with the default config.
 */

class Optimisation {

	/**
	 * Unoptimized function.
	 *
	 * @param String $a
	 * @param String $b
	 */
	function testOptimisation($a, $b) {


		while ($a < count($b)) {
			// 1 - functionInsideLoop : store count($b) in a temp variable :: PHPCHECKSTYLE_FUNCTION_INSIDE_LOOP
			$a++;
		}

		for ($i = 0; $i < count($b); $i++) {
			// 2- functionInsideLoop : store count($b) in a temp variable :: PHPCHECKSTYLE_FUNCTION_INSIDE_LOOP
			echo $i;
		}
	}

}
