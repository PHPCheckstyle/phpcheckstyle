<?php
require_once PHPCHECKSTYLE_HOME_DIR."/src/reporter/XmlFormatReporter.php";

/**
 * Writes the errors into an xml file
 * Format:
 * ================================
 * <checkstyle>
 *    <file name="file1">
 *        <error line="M" column="1" severity="error" message="error message"/>
 *    </file>
 *    <file name="file2">
 *        <error line="X" message="error message"/>
 *        <error line="Y" message="error message"/>
 *    </file>
 *    <file name="file3"/>
 * </checkstyle>
 * ================================
 *
 * @author Hari Kodungallur <hkodungallur@spikesource.com>
 */
class XmlConsoleFormatReporter extends XmlFormatReporter {
	/**
	 * Constructor; calls parent's constructor
	 *
	 * @param $ofolder the folder name
	 */
	public function __construct() {

	}


	/**
	 * @see Reporter::start
	 * add the last element to the tree and save the DOM tree to the
	 * xml file
	 *
	 */
	public function stop() {
		$this->_endCurrentElement();
		echo $this->getDocument()->saveXML();
	}

}

