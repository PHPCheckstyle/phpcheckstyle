<?php
use PHPUnit\Framework\TestCase;

/**
 * Comments tests.
 */
class CommentsTest extends TestCase {

	/**
	 * Test for comments rules.
	 */
	public function testComments() {
		$phpcheckstyle = $GLOBALS['PHPCheckstyle'];

		$phpcheckstyle->processFiles(array(
			'./test/sample/bad_comments.php'
		));

		$errorCounts = $phpcheckstyle->getErrorCounts();

		$this->assertEquals(0, $errorCounts['error'], 'We expect 0 errors');
		$this->assertEquals(0, $errorCounts['ignore'], 'We expect 0 ignored checks');
		$this->assertEquals(0, $errorCounts['info'], 'We expect 0 info');
		$this->assertEquals(6, $errorCounts['warning'], 'We expect 6 warnings');
	}
}
?>