<?php
/**
 * This file is an exemple of PHP file containing bad style (according to the default ruleset).
 */
class BadTest {
?>

<?= "NoShortTags"; ?>

<?php echo "This is a very long line xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"; ?>

<? // The open tag should be <?php

	define("constant", 100); // Incorrect constant naming

	$TOTO; // Incorrect variable naming

	$Toto; // Incorrect variable naming

	$to_to; // Incorrect variable naming

	// Docblock is missing	
	function toto() { // Curly brace should be on same line

		if ($test) {
		}

		if ($a AND $b) { // Use logical operators
		}

		if ($a == $b)
			echo $b; // whitespaces are missing // braces are missing

		if ($a == $b) { // no space before closing parenthesis
		} else { // else should be on the same line
			echo $c;
		}

		if ($a == $b) { // no space after opening parenthesis
		}

		if ($a == $b) {
			b;
		} // closing curly should be on the next line

		$a; // no space before a semicolon

		if (true) { // unnecessary if
		}

		echo " toto $a "; // avoid variables inside strings

		$a = $a - $b; // We should have spaces around the -
		$a = $a * $b; // We should have spaces around the *

		$a = $a && $b;
		;

		# Shell / Perl comments are forbidden

		@doSomething(); // Errors should not be silenced

		exec('toto'); // Forbidden function

		if ($u++) { // Unary operators should be on a single line
		}

		switch ($text) {
		case "a":
			// break is missing
		case "b":
			break;
		case "c":
			break;
			// default is missing
		}
		
		$z = $HTTP_POST_VARS; // deprecated predefined variables

	}

	/**
	 * TODO : // This is a TODO
	 */
	private function privateFunction($a) { // should have a underscore because it is private
		// no space after function name
		// unused function parameter $a
	}

	/**
	 *
	 */
	protected function _protectedFunction($a) { // should not have a underscore because it is protected
		$a;
	}

	/**
	 *
	 */
	public function publicFunction(&$toto) { // parameter passed by reference

		return $a; // the returned parameter should be documented

		$a = 0; // unused code
	}

	/**
	 * @param a
	 * @param b
	 */
	public function publicFunctionBad($a = false, $b) { // default value should be at the end

		if ($a = $b) { // inner assignment instead of comparison
			echo $a;
		}
	}

	/**
	 * Function having an exception.
	 *
	 */
	public function FunctionWithException() { // Function naming incorrect
		try {
			// do something
			throw $e; // Throw an exception that is not documented
		} catch (Exception $e) {
			// Empty catch block
		}
	}

	<<<EOT
  heredoc syntax should be avoided
EOT
	
}

// No docblock
class BadTest2 { // 2 class declaration in the same file
	
	while ($a < count($b)) { // Optimisation : store count($b) in a temp variable
	}
	
	throw new Exception('test');
	
	// unreachable code
	split($a); // deprecated function
	
}
	
// The closing tag is not a good idea	
?>  