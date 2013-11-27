<?php
require_once PHPCHECKSTYLE_HOME_DIR."/src/reporter/HTMLFormatReporter.php";

/**
 * Writes the errors to the console in HTML format.
 */
class HTMLConsoleFormatReporter extends HTMLFormatReporter {

	/**
	 * Constructor; calls parent's constructor
	 */
	public function HTMLConsoleFormatReporter() {
		parent::__construct();
	}

	/**
	 * Writes an HTML fragment to stdout.
	 *
	 * @param $fragment string The HTML fragment to write.
	 */
	protected function writeFragment($fragment) {
		echo $fragment;
	}
}