<?php
/**
 * This file is an exemple of PHP file containing bad indentation.
 * 4 whitespaces are expected for each level of indentation.
 * 
 * This file should generate 6 warnings with the default config.
 */
 class Indentation {

	/**
 	 * Test.
 	 */
	function test() {

    $a = $a; // 4 spaces and a token
	$b = $b; // 1 tab and a token
   $c = $c; // 3 spaces and a token
     $d = $d; // 5 spaces and a token
      $e = $e; // 1 tab, 1 space spaces and a token
 }
}