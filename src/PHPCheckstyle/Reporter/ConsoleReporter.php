<?php
namespace PHPCheckstyle\Reporter;

/**
 * Writes the errors to the console
 * Format:
 * ================================
 * FileName Line X: Error Message
 * FileName Line Y: Error Message
 * ================================
 */
class ConsoleReporter extends Reporter {
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
	 * @SuppressWarnings
	 */
	public function writeError($line, $check, $message, $level = WARNING) {
		echo "File \"" . $this->currentPhpFile . "\"" . " " . $level . ", line " . $line . " - " . $message . "\n";
	}
}
