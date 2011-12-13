<?php

/**
 * This file is an exemple of PHP file containing good style (according to the default ruleset).
 *
 */
class GoodTest {
?>

<?php echo "NoShortTags"; ?>

<?php echo "This is a not so very long line xxxxxxxxxxxx"; ?>

<?php

define("CONSTANT", 100);  // Constant Naming correct

/**
 * This function is documented
 */
function totoTwo(PARAMETER1, PARAMETER11) {
	
	$a, $b, $c = 0;
	$text = "";  // correct variable naming

	if ($a == $b) {
		echo $b;
	} else {
		echo $c;
	}

		
	$a = -12; // Should not ask for a space between - and 12

	echo " toto ".$a;
	
	$a = array(	$a, 
				$b,
				$c   // this used to generate a false positive for noSpaceBeforeToken rule
	);


	switch ($text) {
		case "a":
			switch ($text) {
				case "a":					
					break;
				case "b":
					break;
				case "c":
					break;
				default:
					break;
			}
			break;
		case "b":
			break;
		case "c":
			break;
		default:
			break;
	}

		
}


/**
 * Correctly documented function.
 *
 * @param a
 * @param b
 * @return result
 */
private function _privateFunction($a, $b, $c = false) { // should have a underscore

	// Call the private function toto
	$this->badlyNamedPrivateFunction();
	
	if ($c) {
		return $a + $b;
	} else {
		return $a;
	}
}

var $test;

/**
 * Function having an exception.
 *
 * @throws Exception
 */
public function functionWithException() {
	try {
		// do something
		throw $e;
	} catch (Exception $e) {
		echo $e;
	}

	// Call the private function
	$this->_privateFunction();
	
	$this->test;
}

/**
 * Error of naming, but for a good reason we decide to suppress the warning using an annotation. 
 * @SuppressWarnings privateFunctionNaming
 */
private function badlyNamedPrivateFunction(PARAMETER1, PARAMETER) { // should have a underscore because it is private

	// Call the private function
	$this->_privateFunction();

	do {
		toto();
	} while (true);
	
}