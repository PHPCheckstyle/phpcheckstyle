<?php
namespace PHPCheckstyle\Reporter;

/**
 * Do not output anything.
 */
class NullReporter extends Reporter {
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
		// Do nothing
	}
}
