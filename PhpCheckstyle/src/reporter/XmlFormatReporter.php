<?php
/*
 *  $Id: XmlFormatReporter.php 26740 2005-07-15 01:37:10Z hkodungallur $
 *
 *  Copyright(c) 2004-2005, SpikeSource Inc. All Rights Reserved.
 *  Licensed under the Open Source License version 2.1
 *  (See http://www.spikesource.com/license.html)
 */
if (!defined("PHPCHECKSTYLE_HOME_DIR")) {
	define("PHPCHECKSTYLE_HOME_DIR", dirname(__FILE__) . "/../..");
	define('PHPCHECKSTYLE_HOME_DIR', dirname(__FILE__) . "/../..");
}

require_once PHPCHECKSTYLE_HOME_DIR . "/src/reporter/Reporter.php";


/**
 * Writes the errors into an xml file
 * Format:
 * ================================
 * <phpcheckstyle>
 *    <file name="file1">
 *        <error line="M" message="error message"/>
 *    </file>
 *    <file name="file2">
 *        <error line="X" message="error message"/>
 *        <error line="Y" message="error message"/>
 *    </file>
 *    <file name="file3"/>
 * </phpcheckstyle>
 * ================================
 *
 * @author Hari Kodungallur <hkodungallur@spikesource.com>
 */
class XmlFormatReporter extends Reporter {
	private $document = false;
	private $root = false;
	private $currentElement = false;

	/**
	 * Constructor; calls parent's constructor
	 *
	 * @param $ofile the file name
	 */
	public function XmlFormatReporter($ofile = false) {
		parent::__construct($ofile);
	}

	/**
	 * @see Reporter::start
	 * create the document root (<phpcheckstyle>)
	 *
	 */
	public function start() {
		$this->_initXml();
	}

	/**
	 * @see Reporter::start
	 * add the last element to the tree and save the DOM tree to the
	 * xml file
	 *
	 */
	public function stop() {
		$this->_endCurrentElement();
		$this->document->save($this->outputFile);
	}

	/**
	 * @see Reporter::currentlyProcessing
	 * add the previous element to the tree and start a new elemtn
	 * for the new file
	 *
	 * @param $phpFile the file currently processed
	 */
	public function currentlyProcessing($phpFile) {
		parent::currentlyProcessing($phpFile);
		$this->_endCurrentElement();
		$this->_startNewElement($phpFile);
	}

	/**
	 * @see Reporter::writeError
	 * creates a <error> element for the current doc element
	 *
	 * @param $line line number of the error
	 * @param $message error message
	 * @param $level the severity level
	 */
	public function writeError($line, $message, $level = WARNING) {
		$e = $this->document->createElement("error");
		$e->setAttribute("line", $line);
		$e->setAttribute("severity", $level);
		$e->setAttribute("message", $message);
		$e->setAttribute("source", "http://code.google.com/p/phpcheckstyle"); // en dur ...
		$this->currentElement->appendChild($e);
	}

	private function _initXml() {
		$this->document = new DomDocument("1.0");
		$this->root = $this->document->createElement('checkstyle');
		$this->document->appendChild($this->root);
	}

	private function _startNewElement($f) {
		$this->currentElement = $this->document->createElement("file");

		// remove the "./" at the beginning ot the path
		$this->currentElement->setAttribute("name", substr($f, 2));

	}

	private function _endCurrentElement() {
		if ($this->currentElement) {
			$this->root->appendChild($this->currentElement);
		}
	}
}

