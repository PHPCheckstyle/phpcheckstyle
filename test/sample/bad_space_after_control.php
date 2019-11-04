<?php

/**
 * This file is an exemple of PHP file containing bad spacing around control statement.
 * This file should generate 1 warning.
 *
 * @SuppressWarnings localScopeVariableLength
 */
class Space {

	/**
	 * Test spacing
	 *
	 * @param String $a
	 * @param String $b
	 * @return String
	 */
	function testSpaces($a, $b) {
		if ($a === null) { // 1 - noSpaceAfterControlStmt (if statement)
			return $b;
		}
	}
}