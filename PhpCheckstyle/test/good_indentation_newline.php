<?php
/**
 * Test same line indentation
 */
class Indentation {

	/**
	 * Test
	 */
	function foo()
	{
		//It wants this bracket to be indented
		$a = 0;

		//code
		if ($a == 1)
		{
			// new code
			echo "toto";
		}

	} //And this bracket too

}