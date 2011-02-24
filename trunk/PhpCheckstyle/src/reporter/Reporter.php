<?php
/*
 *  $Id: Reporter.php 26734 2005-07-15 01:34:26Z hkodungallur $
 *
 *  Copyright(c) 2004-2005, SpikeSource Inc. All Rights Reserved.
 *  Licensed under the Open Source License version 2.1
 *  (See http://www.spikesource.com/license.html)
 */

/**
 * Abstract base class for any type of report generators
 * writeError function is abstract, which will need to be implemented
 * by the deriving class. Also implement start and stop functions
 *
 * @author Hari Kodungallur <hkodungallur@spikesource.com>
 */
abstract class Reporter {

	protected $currentPhpFile;
	protected $outputFile;

	/**
	 * Constructor
	 * Intializes variables. Note that the output file is initialized
	 * to stdout if not provided
	 *
	 * @param $ofile = false
	 */
	public function Reporter($ofile = false) {
		$this->outputFile = $ofile;
		if (!$this->outputFile) {
			$this->outputFile = "php://output";
		}
	}

	/**
	 * Any initialization before starting to write should be done here
	 */
	public function start() {
	}

	/**
	 * Any cleanup work before closing should be done here.
	 */
	public function stop() {
	}

	/**
	 * this function called everytime a new file has been started for
	 * checkstyle processing.
	 *
	 * @param $phpFile new file's name
	 */
	public function currentlyProcessing($phpFile) {
		$this->currentPhpFile = $phpFile;
	}

	/**
	 * abstract function.
	 * For every error, this function is called once with the line where
	 * the error occurred and the actual error message
	 * It is the responsibility of the derived class to appropriately
	 * format it and write it into the output file
	 *
	 * @param $line line number of the error
	 * @param String $check the name of the check
	 * @param $message error message
	 * @param $level the severity level
	 */
	public abstract function writeError($line, $check, $message, $level = WARNING);

}
