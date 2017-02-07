<?php
use PHPUnit\Framework\TestCase;

/**
 * Spaces tests.
 */
class SpacesTest extends TestCase {

	/**
	 * Test for for spaces missing or in excedent.
	 */
	public function testSpaces() {
		$phpcheckstyle = $GLOBALS['PHPCheckstyle'];

		$phpcheckstyle->processFiles(array(
			'./test/sample/bad_spaces.php'
		));

		$errorCounts = $phpcheckstyle->getErrorCounts();

		$this->assertEquals(0, $errorCounts['error'], 'We expect 0 errors of naming');
		$this->assertEquals(0, $errorCounts['ignore'], 'We expect 0 ignored checks');
		$this->assertEquals(2, $errorCounts['info'], 'We expect 2 info');
		$this->assertEquals(7, $errorCounts['warning'], 'We expect 7 warnings');
	}


}
?>