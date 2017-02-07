<?php
use PHPUnit\Framework\TestCase;

/**
 * Other tests.
 */
class OtherTest extends TestCase {

	/**
	 * Test others rules.
	 */
	public function testOther() {
		$phpcheckstyle = $GLOBALS['PHPCheckstyle'];

		$phpcheckstyle->processFiles(array(
			'./test/sample/bad_other.php'
		));

		$errorCounts = $phpcheckstyle->getErrorCounts();

		$this->assertEquals(0, $errorCounts['error'], 'We expect 0 errors of naming');
		$this->assertEquals(0, $errorCounts['ignore'], 'We expect 0 ignored checks');
		$this->assertEquals(0, $errorCounts['info'], 'We expect 0 info');
		$this->assertEquals(20, $errorCounts['warning'], 'We expect 20 warnings');
	}


}
?>