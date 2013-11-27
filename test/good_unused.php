<?php
/**
 * This file is an exemple of PHP file with all variables used.
 * Test a false positive.
 */
class Used {

	/**
	 * TOTO
	 * @var Integer
	 */
	var $toto = null;
	
	/**
	 * @return String
	 */
	function testUnused() {

		$a = 2;
		$result = $a + $this->toto;

		return $result;

	}

}


$view = "test";
?>

Some HTML

<?=$view;?>

<?php 