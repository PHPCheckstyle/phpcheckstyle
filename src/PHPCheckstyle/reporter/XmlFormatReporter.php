<?php
namespace PHPCheckstyle\Reporter;
use DomDocument;

/**
 * Writes the errors into an xml file.
 * 
 * Format:
 * ================================
 * <checkstyle>
 * <file name="file1">
 * <error line="M" column="1" severity="error" message="error message"/>
 * </file>
 * <file name="file2">
 * <error line="X" message="error message"/>
 * <error line="Y" message="error message"/>
 * </file>
 * <file name="file3"/>
 * </checkstyle>
 * ================================
 *
 * @author Hari Kodungallur <hkodungallur@spikesource.com>
 */
class XmlFormatReporter extends Reporter {

	private $document = false;

	private $root = false;

	private $currentElement = false;

	private $ofile = "/style-report.xml"; // The output file name
	
	/**
	 * Constructor; calls parent's constructor
	 *
	 * @param $ofolder the
	 *        	folder name
	 */
	public function __construct($ofolder = false) {
		parent::__construct($ofolder, $this->ofile);
	}

	/**
	 *
	 * @see Reporter::start create the document root (<phpcheckstyle>)
	 *     
	 */
	public function start() {
		$this->_initXml();
	}

	/**
	 *
	 * @see Reporter::start add the last element to the tree and save the DOM tree to the
	 *      xml file
	 *     
	 */
	public function stop() {
		$this->_endCurrentElement();
		$this->document->save($this->outputFile);
	}

	/**
	 *
	 * @see Reporter::currentlyProcessing add the previous element to the tree and start a new element
	 *      for the new file
	 *     
	 * @param $phpFile the
	 *        	file currently processed
	 */
	public function currentlyProcessing($phpFile) {
		parent::currentlyProcessing($phpFile);
		$this->_endCurrentElement();
		$this->_startNewElement($phpFile);
	}

	/**
	 *
	 * @see Reporter::writeError creates a <error> element for the current doc element
	 *     
	 * @param Integer $line
	 *        	the line number
	 * @param String $check
	 *        	the name of the check
	 * @param String $message
	 *        	error message
	 * @param String $level
	 *        	the severity level
	 */
	public function writeError($line, $check, $message, $level = WARNING) {
		$e = $this->document->createElement("error");
		$e->setAttribute("line", $line);
		$e->setAttribute("column", "1");
		$e->setAttribute("severity", $level);
		$e->setAttribute("message", $message);
		$e->setAttribute("source", $check);
		
		if (empty($this->currentElement)) {
			$this->_startNewElement("");
		}
		$this->currentElement->appendChild($e);
	}

	protected function _initXml() {
		$this->document = new DomDocument("1.0");
		$this->root = $this->document->createElement('checkstyle');
		$this->root->setAttribute("version", "1.0.0");
		$this->document->appendChild($this->root);
	}

	protected function _startNewElement($f) {
		$this->currentElement = $this->document->createElement("file");
		
		// remove the "./" at the beginning ot the path in case of relative path
		if (substr($f, 0, 2) == './') {
			$f = substr($f, 2);
		}
		$this->currentElement->setAttribute("name", $f);
	}

	protected function getDocument() {
		return $this->document;
	}

	protected function _endCurrentElement() {
		if ($this->currentElement) {
			$this->root->appendChild($this->currentElement);
		}
	}
}

