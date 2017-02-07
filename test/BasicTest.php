<?php
use PHPUnit\Framework\TestCase;

/**
 * Base tests.
 */
class BasicTest extends TestCase {

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


	/**
	 * Test aliases for depecated methodes rule.
	 */
	public function testAlias() {
		$phpcheckstyle = $GLOBALS['PHPCheckstyle'];

		$phpcheckstyle->processFiles(array(
			'./test/sample/bad_alias.php'
		));

		$errorCounts = $phpcheckstyle->getErrorCounts();

		$this->assertEquals(0, $errorCounts['error'], 'We expect 0 errors');
		$this->assertEquals(0, $errorCounts['ignore'], 'We expect 0 ignored checks');
		$this->assertEquals(0, $errorCounts['info'], 'We expect 0 info');
		$this->assertEquals(3, $errorCounts['warning'], 'We expect 5 warnings');
	}

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



	/**
	 * Test for deprecated php methods rules.
	 */
	public function testDeprecations() {
		$phpcheckstyle = $GLOBALS['PHPCheckstyle'];

		$phpcheckstyle->processFiles(array(
			'./test/sample/bad_deprecation.php'
		));

		$errorCounts = $phpcheckstyle->getErrorCounts();

		$this->assertEquals(0, $errorCounts['error'], 'We expect 0 errors');
		$this->assertEquals(0, $errorCounts['ignore'], 'We expect 0 ignored checks');
		$this->assertEquals(0, $errorCounts['info'], 'We expect 0 info');
		$this->assertEquals(5, $errorCounts['warning'], 'We expect 5 warnings');
	}


	/**
	 * Test tabs indentation.
	 */
	public function testTabIndentation() {
		$phpcheckstyle = $GLOBALS['PHPCheckstyle'];

		$phpcheckstyle->processFiles(array(
			'./test/sample/bad_indentation.php'
		));

		$errorCounts = $phpcheckstyle->getErrorCounts();

		$this->assertEquals(0, $errorCounts['error'], 'We expect 0 errors');
		$this->assertEquals(0, $errorCounts['ignore'], 'We expect 0 ignored checks');
		$this->assertEquals(0, $errorCounts['info'], 'We expect 0 info');
		$this->assertEquals(6, $errorCounts['warning'], 'We expect 6 warnings');
	}

	/**
	 * Test tabs indentation.
	 */
	public function testSpaceIndentation() {
		$phpcheckstyle = $GLOBALS['PHPCheckstyle'];

		// Change the configuration to check for spaces instead of tabs
		$phpcheckstyle->getConfig()->setTestProperty('indentation', 'type', 'spaces');

		$phpcheckstyle->processFiles(array(
			'./test/sample/bad_indentation.php'
		));

		$errorCounts = $phpcheckstyle->getErrorCounts();

		$this->assertEquals(0, $errorCounts['error'], 'We expect 0 errors');
		$this->assertEquals(0, $errorCounts['ignore'], 'We expect 0 ignored checks');
		$this->assertEquals(0, $errorCounts['info'], 'We expect 0 info');
		$this->assertEquals(11, $errorCounts['warning'], 'We expect 11 warnings');
	}


}
?>