<?php
use PHPUnit\Framework\TestCase;

/**
 * Naming tests.
 */
class NamingTest extends TestCase {

	/**
	 * Test naming rules.
	 */
	public function testNaming() {
		$phpcheckstyle = $GLOBALS['PHPCheckstyle'];

		$phpcheckstyle->processFiles(array(
			'./test/sample/_bad_naming.php'
		));

		$errorCounts = $phpcheckstyle->getErrorCounts();

		// echo print_r($errorCounts, true);

		$this->assertEquals(8, $errorCounts['error'], 'We expect 8 errors of naming');
		$this->assertEquals(0, $errorCounts['ignore'], 'We expect 0 ignored checks');
		$this->assertEquals(0, $errorCounts['info'], 'We expect 0 info');
		$this->assertEquals(5, $errorCounts['warning'], 'We expect 5 warnings');
	}
}
?>