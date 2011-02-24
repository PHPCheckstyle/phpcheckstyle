<?php

require_once PHPCHECKSTYLE_HOME_DIR."/src/reporter/Reporter.php";

/**
 * Writes the errors into an HTML file.
 */
class HTMLFormatReporter extends Reporter {

	private $files = '';
	private $detail = '';
	private $fileDetail = ''; // The list of checks in error for the current file

	private $nbfiles = 0; // Total number of files scanned
	private $fileInError = false; // Is the current file in error
	private $nbfilesError = 0; // Number of files containing an erro
	private $nbErrors = 0; // Total number of errors
	private $fileErrors = 0; // Number of errors in the current file

	private $previousFile = '';

	/**
	 * Constructor; calls parent's constructor
	 *
	 * @param $ofile the file name
	 */
	public function HTMLFormatReporter($ofile = false) {
		parent::__construct($ofile);
	}

	/**
	 * @see Reporter::start
	 */
	public function start() {
		parent::start();
	}

	/**
	 * @see Reporter::stop
	 */
	public function stop() {
		$fileHandle = fopen($this->outputFile, "w");

		// Call a last time the currentlyProcessing function for the last file in memory
		$this->currentlyProcessing('');

		// Write the HTML header
		fwrite($fileHandle, $this->_readTemplate("header"));

		// Write the summary
		$summaryTmpl = $this->_readTemplate("summary");
		$values = array();
		$values['%%nb_files%%'] = $this->nbfiles;
		$values['%%nb_files_error%%'] = $this->nbfilesError;
		$values['%%nb_total_errors%%'] = $this->nbErrors;
		fwrite($fileHandle, $this->_fillTemplate($summaryTmpl, $values));

		// Write the list of files
		fwrite($fileHandle, $this->_readTemplate("files"));
		fwrite($fileHandle, $this->files);
		fwrite($fileHandle, $this->_readTemplate("files_foot"));

		// Write the detail of the checks
		fwrite($fileHandle, $this->_readTemplate("detail"));
		fwrite($fileHandle, $this->detail);

		// Write the footer
		fwrite($fileHandle, $this->_readTemplate("footer"));

		fclose($fileHandle);
	}

	/**
	 * Read the content of a template file.
	 *
	 * @param $templateFile The name of the file
	 */
	private function _readTemplate($templateFile) {
		$filename = PHPCHECKSTYLE_HOME_DIR."/html/template/".$templateFile.".tmpl";
		$handle = fopen($filename, "r");
		$contents = fread($handle, filesize($filename));
		fclose($handle);

		return $contents;
	}

	/**
	 * Replace some values in a template file.
	 *
	 *  @param $template The content of the template
	 *  @param $values The array of values to replace
	 */
	private function _fillTemplate($template, $values) {

		foreach ($values as $key => $value) {
			$template = str_replace($key, $value, $template);
		}

		return $template;
	}

	/**
	 * @see Reporter::currentlyProcessing
	 * Process a new PHP file.
	 *
	 * @param $phpFile the file currently processed
	 */
	public function currentlyProcessing($phpFile) {

		// Update the counters
		$this->nbfiles++;

		// If the previous file contained errors
		if ($this->fileErrors != 0) {

			// Add the previous file to the summary
			if ($this->previousFile != '') {
				$fileBody = $this->_readTemplate("files_body");
				$values = array();
				$values['%%filepath%%'] = $this->previousFile;
				$values['%%file_errors%%'] = $this->fileErrors;
				$fileBody = $this->_fillTemplate($fileBody, $values);
				$this->files .= $fileBody;
			}

			// Add the previous file to the details
			$detailHead = $this->_readTemplate("detail_head");
			$values = array();
			$values['%%filepath%%'] = $this->previousFile;
			$detailHead = $this->_fillTemplate($detailHead, $values);
			$this->detail .= $detailHead;
			$this->detail .= $this->fileDetail;
			$this->detail .= $this->_readTemplate("detail_foot");
		}

		$this->previousFile = $phpFile;
		$this->fileDetail = '';
		$this->fileErrors = 0;
		$this->fileInError = false;

	}

	/**
	 * @see Reporter::writeError
	 * Write a new check error.
	 *
	 * @param Integer $line the line number
	 * @param String $check the name of the check
	 * @param String $message the text
	 * @param String $level the severity level
	 */
	public function writeError($line, $check, $message, $level = WARNING) {

		// Update the counters
		$this->nbErrors++;
		$this->fileErrors++;
		if ($this->fileInError == false) {
			$this->nbfilesError++;
			$this->fileInError = true;
		}

		// Add the check to the details
		$detail = $this->_readTemplate("detail_body");
		$values = array();
		$values['%%message%%'] = htmlspecialchars($message);
		$values['%%check%%'] = $check;
		$values['%%line%%'] = $line;
		$values['%%level%%'] = $level;
		$detail = $this->_fillTemplate($detail, $values);
		$this->fileDetail .= $detail;

	}
}

