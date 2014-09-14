<?php
namespace PHPCheckstyle\Reporter;

/**
 * Writes the errors to an array.
 *
 * @author James Brooks <jbrooksuk@me.com>
 */
class ArrayReporter extends Reporter {

	public function __construct() {
		$this->outputFile = array();
	}

	/**
	 *
	 * @see Reporter::writeError Tab the line and write the error message
	 *
	 * @param Integer $line
	 *        	the line number
	 * @param String $check
	 *        	the name of the check
	 * @param String $message
	 *        	the text to log
	 * @param String $level
	 *        	the severity level
	 */
	public function writeError($line, $check, $message, $level = WARNING) {
		// echo $this->currentPhpFile . " " . $level . " Line:" . $line . " - " . $message . "\n";
		$this->outputFile[$this->currentPhpFile][$line][] = array(
			"level"   => $level,
			"line"    => $line,
			"message" => $message,
			"check"   => $check,
		);
	}
}
