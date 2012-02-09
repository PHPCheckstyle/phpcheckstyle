<?php
/*
 *  $Id: PHPCheckstyle.php 28215 2005-07-28 02:53:05Z hkodungallur $
*
*  Copyright(c) 2004-2005, SpikeSource Inc. All Rights Reserved.
*  Licensed under the Open Source License version 2.1
*  (See http://www.spikesource.com/license.html)
*/

if (!defined("PHPCHECKSTYLE_HOME_DIR")) {
	define("PHPCHECKSTYLE_HOME_DIR", dirname(__FILE__)."/..");
}

require_once PHPCHECKSTYLE_HOME_DIR."/src/CheckStyleConfig.php";
require_once PHPCHECKSTYLE_HOME_DIR."/src/styleErrors.inc.php";
require_once PHPCHECKSTYLE_HOME_DIR."/src/errorLevels.inc.php";
require_once PHPCHECKSTYLE_HOME_DIR."/src/TokenUtils.php";
require_once PHPCHECKSTYLE_HOME_DIR."/src/TokenInfo.php";
require_once PHPCHECKSTYLE_HOME_DIR."/src/StatementItem.php";
require_once PHPCHECKSTYLE_HOME_DIR."/src/reporter/Reporters.php";
require_once PHPCHECKSTYLE_HOME_DIR."/src/reporter/PlainFormatReporter.php";
require_once PHPCHECKSTYLE_HOME_DIR."/src/reporter/XmlFormatReporter.php";
require_once PHPCHECKSTYLE_HOME_DIR."/src/reporter/HTMLConsoleFormatReporter.php";
require_once PHPCHECKSTYLE_HOME_DIR."/src/reporter/HTMLFormatReporter.php";
require_once PHPCHECKSTYLE_HOME_DIR."/src/reporter/ConsoleReporter.php";
require_once PHPCHECKSTYLE_HOME_DIR."/src/reporter/XmlNCSSReporter.php";
require_once PHPCHECKSTYLE_HOME_DIR."/src/util/Utility.php";

if (!defined("T_ML_COMMENT")) {
	define("T_ML_COMMENT", T_COMMENT);
}

/**
 * Main Class. Does most of the parsing and processing
 *
 * @author Hari Kodungallur <hkodungallur@spikesource.com>
 */
class PHPCheckstyle {
	// Variables

	/**
	 * @var TokenUtils
	 */
	private $tokenizer;
	private $validExtensions = array("php", "tpl");

	// variables used while processing control structure
	private $_csLeftParenthesis = 0; // Left brackets opened in control statement or function statement
	private $_fcLeftParenthesis = 0; // Left brackets opened in function call
	private $inDoWhile = false;

	private $token = false;
	private $prvsToken = false;
	private $lineNumber = 0; // Store the current line number

	private $_isLineStart = true; // Start of a line (just after a return)

	// Indicate if we are in a control statement declaration (for, if, while, ...)
	// The control statement starts just after the statement token
	// and stops at the closing of the parenthesis or the new line if no parenthesis is used
	private $_inControlStatement = false;

	private $_inString = false;
	private $_stringStartCharacter;
	private $_inArrayStatement = false; // We are in a array statement
	private $_inClassStatement = false; // Wa are in a class statement (declaration)
	private $_inInterfaceStatement = false; // Wa are in an interface statement (declaration)
	private $_inFunctionStatement = false; // We are in a function statement (declaration)
	private $_inFuncCall = false; // We are in a function call
	private $_inFunction = false; // We are inside a function
	private $_inClass = false; // We are inside a class
	private $_inInterface = false; // We are inside an interface
	private $_privateFunctions = array(); // The list of private functions in the class
	private $_privateFunctionsStartLines = array();
	private $_functionParameters = array(); // The list of function parameters
	private $_usedFunctions = array(); // The list of functions that are used in the class
	private $_variables = array(); // The variables used
	private $_inSwitch = false; // We are inside a switch statement
	private $_nbFunctionParameters = 0; // Count the number of parameters of the current function
	private $_justAfterFuncStmt = false; // We are just after a control statement (last } )
	private $_justAfterControlStmt = false; // We are just after a function statement (last } )
	private $_functionStartLine = 0; // Starting line of the current function
	private $_switchStartLine = 0; // Starting line of the current switch statement
	private $_functionReturns = false; // Does the function return a value ?
	private $_functionThrows = false; // Does the function throw an exception ?
	private $_functionLevel = 0; // Level of Nesting of the function
	private $_functionVisibility = 'PUBLIC'; // PUBLIC, PRIVATE or PROTECTED
	private $_classLevel = 0; // Level of Nesting of the class
	private $_interfaceLevel = 0; // Level of Nesting of the interface
	private $_constantDef = false;
	private $_currentClassname = null;
	private $_currentInterfacename = null;
	private $_currentFilename = null;
	private $_currentStatement = false;
	private $_currentFunctionName = null;

	private $_docblocNbParams = 0; // Number of @params in the docblock of a function
	private $_docblocNbReturns = 0; // Number of @return in the docblock of a function
	private $_docblocNbThrows = 0; // Number of @throw in the docblock of a function

	private $_branchingStack = array();  // Array of StatementItem - The stack of currently started statements.
	private $_cyclomaticComplexity = 0;

	private $_fileSuppressWarnings = array(); // List of warnings to ignore for this file
	private $_classSuppressWarnings = array(); // List of warnings to ignore for this class
	private $_interfaceSuppressWarnings = array(); // List of warnings to ignore for this interface
	private $_functionSuppressWarnings = array(); // List of warnings to ignore for this function

	// For MVC frameworks
	private $_isView = false;
	private $_isModel = false;
	private $_isController = false;
	private $_isClass = false;

	/**
	 * These functions are not tested for naming.
	 */
	private $_specialFunctions = array();

	private $_prohibitedFunctions = array('echo', 'system', "print_r", 'dl',
		'exec', 'passthru', 'shell_exec',
		'copy', 'delete', 'unlink',
		'fwrite');

	private $_prohibitedTokens = array('T_BAD_CHARACTER', 'T_DECLARE',
		'T_ECHO', 'T_ENDDECLARE', 'T_ENDFOR',
		'T_ENDFOREACH', 'T_ENDIF',
		'T_ENDSWITCH', 'T_ENDWHILE',
		'T_END_HEREDOC', 'T_EXIT',
		'T_HALT_COMPILER', 'T_INLINE_HTML',
		'T_OLD_FUNCTION',
		'T_OPEN_TAG_WITH_ECHO', 'T_PRINT');

	private $_deprecatedFunctions = array();

	private $_systemVariables = array();


	// The class used to export the result
	private $_reporter;

	// The class used to export the count of lines
	private $_lineCountReporter;

	private $_excludeList = array();

	private $_config;

	// Informations used to count lines of code
	private $_ncssTotalClasses = 0;
	private $_ncssTotalInterfaces = 0;
	private $_ncssTotalFunctions = 0;
	private $_ncssTotalLinesOfCode = 0;
	private $_ncssTotalPhpdoc = 0;
	private $_ncssTotalLinesPhpdoc = 0;
	private $_ncssTotalSingleComment = 0;
	private $_ncssTotalMultiComment = 0;
	private $_ncssFileClasses = 0;
	private $_ncssFileInterfaces = 0;
	private $_ncssFileFunctions = 0;
	private $_ncssFileLinesOfCode = 0;
	private $_ncssFilePhpdoc = 0;
	private $_ncssFileLinesPhpdoc = 0;
	private $_ncssFileSingleComment = 0;
	private $_ncssFileMultiComment = 0;

	// Whether or not the progress display is shown.
	private $_displayProgress = false;

	/**
	 * Constructor.
	 *
	 * @param String $formats Array of output formats ("text", "html", "console", ...)
	 * 					Accordingly creates the formatter objects
	 * @param String $outfile  output file where results are stored.
	 * 					Note that in case of "html" format, the output is xml and run.php transforms the xml file into html
	 * @param String $linecountfile output file where line counts are stored
	 * @access public
	 */
	public function PHPCheckstyle($formats, $outDir, $linecountfile = null, $progress = false) {

		// Initialise the Tokenizer
		$this->tokenizer = new TokenUtils();

		// Initialise the Reporters
		$this->_reporter = new Reporters();
		if (in_array("text", $formats)) {
			$this->_reporter->addReporter(new PlainFormatReporter($outDir));
		}
		if (in_array("html", $formats)) {
			$this->_reporter->addReporter(new HTMLFormatReporter($outDir));
		}
		if (in_array("html_console", $formats)) {
			$this->_reporter->addReporter(new HTMLConsoleFormatReporter());
		}
		if (in_array("xml", $formats)) {
			$this->_reporter->addReporter(new XmlFormatReporter($outDir));
		}
		if (in_array("console", $formats)) {
			$this->_reporter->addReporter(new ConsoleReporter());
		}
		if ($linecountfile != null) {
			$this->_lineCountReporter = new XmlNCSSReporter($outDir, $linecountfile);
		}

		// Initialize progress reporting
		$this->_displayProgress = $progress;

		// Initialise the configuration
		$this->_config = new CheckStyleConfig("");
		$this->_config->parse();

		// Load the list of system variables
		$this->_systemVariables = $this->_config->getConfigItems('systemVariables');

		// Load the list of special functions
		$this->_specialFunctions = $this->_config->getConfigItems('specialFunctions');

		// Load the list of forbidden functions
		$this->_prohibitedFunctions = $this->_config->getTestItems('checkProhibitedFunctions');

		// Load the list of forbidden tokens
		$this->_prohibitedTokens = $this->_config->getTestItems('checkProhibitedTokens');

		// Load the list of deprecated function
		$this->_deprecatedFunctions = $this->_config->getTestDeprecations('checkDeprecation');


	}

	/**
	 * driver function that call processFile repeatedly for each php
	 * file that is encountered
	 *
	 * @param $src a php file or a directory. in case of directory, it
	 *        searches for all the php/tpl files within the directory
	 *        (recursively) and each of those files are processed
	 * @param $excludes an array of directories or files that need to be
	 *        excluded from processing
	 * @return nothing
	 * @access public
	 */
	public function processFiles($src, $excludes) {
		$this->_excludeList = $excludes;

		$roots = explode(",", $src);
		$files = array();

		foreach ($roots as $root) {
			$files = array_merge($files, $this->_getAllPhpFiles($root, $excludes));
		}

		// Start reporting the results
		$this->_reporter->start();

		// Start counting the lines
		if ($this->_lineCountReporter != null) {
			$this->_lineCountReporter->start();
		}

		// Process each file
		foreach ($files as $file) {
			if (is_array($file)) {
				continue;
			}
			$this->_reporter->currentlyProcessing($file);
			$this->_processFile($file);
		}

		// Stop reporting the results
		$this->_reporter->stop();

		// Write the count of lines for the complete project
		if ($this->_lineCountReporter != null) {
			$this->_lineCountReporter->writeTotalCount(count($files),
			$this->_ncssTotalClasses,
			$this->_ncssTotalInterfaces,
			$this->_ncssTotalFunctions,
			$this->_ncssTotalLinesOfCode,
			$this->_ncssTotalPhpdoc,
			$this->_ncssTotalLinesPhpdoc,
			$this->_ncssTotalSingleComment,
			$this->_ncssTotalMultiComment);
		}

		// Stop counting the lines
		if ($this->_lineCountReporter != null) {
			$this->_lineCountReporter->stop();
		}

	}

	/**
	 * Reset the state of the different flags.
	 */
	private function _resetValues() {

		$this->lineNumber = 1;

		// Reset the current attributes
		$this->_csLeftParenthesis = 0;
		$this->_fcLeftParenthesis = 0;
		$this->inDoWhile = false;

		$this->_branchingStack = array();
		$this->_inControlStatement = false;
		$this->_inArrayStatement = false;
		$this->_inFunctionStatement = false;
		$this->_inFunction = false;
		$this->_privateFunctions = array();
		$this->_usedFunctions = array();
		$this->_variables = array();
		$this->_privateFunctionsStartLines = array();
		$this->_inSwitch = false;
		$this->_inFuncCall = false;
		$this->_nbFunctionParameters = 0;
		$this->_justAfterFuncStmt = false;
		$this->_justAfterControlStmt = false;
		$this->_functionStartLine = 0;
		$this->_functionReturns = false;
		$this->_functionThrows = false;
		$this->_functionVisibility = 'PUBLIC';
		$this->_currentStatement = false;
		$this->_inClassStatement = false;
		$this->_inInterfaceStatement = false;

		$this->__constantDef = false;

		$this->_ncssFileClasses = 0;
		$this->_ncssFileInterfaces = 0;
		$this->_ncssFileFunctions = 0;
		$this->_ncssFileLinesOfCode = 0;
		$this->_ncssFilePhpdoc = 0;
		$this->_ncssFileLinesPhpdoc = 0;
		$this->_ncssFileSingleComment = 0;
		$this->_ncssFileMultiComment = 0;

		$this->_currentFunctionName = null;
		$this->_currentClassname = null;
		$this->_currentInterfacename = null;
		$this->_currentFilename = null;

		$this->_docblocNbParams = 0;
		$this->_docblocNbReturns = 0;
		$this->_docblocNbThrows = 0;

		$this->_isView = false;
		$this->_isModel = false;
		$this->_isController = false;
		$this->_isClass = false;
	}

	/**
	 * Process one php file
	 *
	 * @param $f input file
	 * @return  nothing
	 * @access private
	 */
	private function _processFile($f) {

		if (DEBUG) {
			echo "Processing File : ".$f.PHP_EOL;
		}

		// Reset the tokenizer
		$this->tokenizer->reset();

		// Reset the state of the attributes
		$this->_resetValues();

		// Try to detect the type of file in a MVC framework
		if (stripos($f, 'view') !== false || stripos($f, 'layouts') !== false) {
			$this->_isView = true;
		}
		if (stripos($f, 'model') !== false) {
			$this->_isModel = true;
		}
		if (stripos($f, 'controller') !== false) {
			$this->_isController = true;
		}
		if (stripos($f, 'class') !== false) {
			// simple simple data objects
			$this->_isClass = true;
		}

		$this->_currentFilename = $f;

		// Tokenize the file
		$this->tokenizer->tokenize($f);

		// Go to the first token
		$this->_moveToken();

		// Run through every token of the file
		while ($this->token) {

			if (is_array($this->token)) {
				// The token is an array
				list($tok, $text) = $this->token;
				$this->_processToken($text, $tok);
			} else if (is_string($this->token)) {
				// The token is a String
				$text = $this->token;
				$this->_processString($text);
			}

			// Go to the next token
			$this->_moveToken();
		}

		// Test the last token of the file
		if ($this->_isActive('noFileCloseTag')) {
			if ($this->tokenizer->checkProvidedToken($this->prvsToken, T_CLOSE_TAG)) {
				// Closing tag is not recommended since PHP 5.0
				$this->_writeError('noFileCloseTag', PHPCHECKSTYLE_END_FILE_CLOSE_TAG);
			}
		}

		// Inner HTML is OK for views but not for other classes (controllers, models, ...)
		if ($this->_isActive('noFileFinishHTML') && !$this->_isView) {
			if ($this->tokenizer->checkProvidedToken($this->prvsToken, T_INLINE_HTML)) {
				$this->_writeError('noFileFinishHTML', PHPCHECKSTYLE_END_FILE_INLINE_HTML);
			}
		}

		// Check for unused private functions
		$this->_checkUnusedPrivateFunctions();

		// Check for unused variables
		$this->_checkUnusedVariables();

		if ($this->_ncssFileClasses > 0 || $this->_ncssFileInterfaces > 0) {
			// Test the file name, only if it contains a class or onterface
			$this->_checkFileNaming();
		}

		// Write the count of lines for this file
		if ($this->_lineCountReporter != null) {
			$this->_lineCountReporter->writeFileCount($f, $this->_ncssFileClasses, $this->_ncssFileInterfaces, $this->_ncssFileFunctions, $this->_ncssFileLinesOfCode, $this->_ncssFilePhpdoc, $this->_ncssFileLinesPhpdoc, $this->_ncssFileSingleComment, $this->_ncssFileMultiComment);
		}

		// Reset the suppression warnings
		$this->_fileSuppressWarnings = array();
		$this->_classSuppressWarnings = array();
		$this->_interfaceSuppressWarnings = array();

	}

	/**
	 * Go through a directory recursively and get all the
	 * php (with extension .php and .tpl) files
	 * Ignores files or subdirectories that are in the _excludeList
	 *
	 * @param String $src source directory
	 * @param Array $excludes paths to exclude
	 * @param String $dir the base directory
	 * @return an array of php files
	 * @access private
	 */
	private function _getAllPhpFiles($src, $excludes, $dir = '') {

		$files = array();
		if (!is_dir($src)) {

			// Source is a file
			$isExcluded = false;
			foreach ($excludes as $patternExcluded) {
				if (strstr($src, $patternExcluded)) {
					$isExcluded = true;
				}
			}
			if (!$isExcluded) {
				$files[] = $src;
			}
		} else {

			// Source is a directory
			$root = opendir($src);
			if ($root) {
				while ($file = readdir($root)) {

					// We ignore the current and parent directory links
					if ($file == "." || $file == "..") {
						continue;
					}
					// We ignore the subversion directories
					if ($file == ".svn") {
						continue;
					}
					$fullPath = $src."/".$file;
					$relPath = substr($fullPath, strlen($src) - strlen($dir) + 1);
					$isExcluded = false;
					foreach ($excludes as $patternExcluded) {
						if (strstr($relPath, $patternExcluded)) {
							$isExcluded = true;
						}
					}

					if (!$isExcluded) {
						if (is_dir($src."/".$file)) {
							$files = array_merge($files, $this->_getAllPhpFiles($src."/".$file, $excludes, $dir.'/'.$file));
						} else {
							$pathParts = pathinfo($file);
							if (array_key_exists('extension', $pathParts)) {
								if (in_array($pathParts['extension'], $this->validExtensions)) {
									$files[] = $src."/".$file;
								}
							}
						}
					}
				}
			}
		}

		return $files;
	}

	/**
	 * Processes a simple string token.
	 *
	 * @param $text the token string
	 * @return nothing
	 * @access private
	 */
	private function _processString($text) {

		if (DEBUG) {
			echo count($this->_branchingStack);
			echo "-".$this->tokenizer->getCurrentPosition();
			echo " Line  : ".$this->lineNumber;
			echo " : ".$text.PHP_EOL;
			$this->_dumpStack();
		}

		// by defaut any token means we are not at the start of the line
		$this->_isLineStart = false;

		switch ($text) {




			case "{":

				// "{" signifies beginning of a block. We need to look for
				// its position when it is a beginning of a control structure
				// or a function or class definition.

				// Check we have a white space before a curly opening in case of a "same line" indentation
				if ($this->_config->getTestProperty('funcDefinitionOpenCurly', 'position') == "sl") {
					$this->_checkWhiteSpaceBefore($text);
				}
				$stackitem = new StatementItem();
				$stackitem->line = $this->lineNumber;

				//FIXME: [Justin] Move this into a constructor

				// if _justAfterFuncStmt is set, the "{" is the beginning of a function definition block
				if ($this->_justAfterFuncStmt) {
					$this->_processFunctionStart();
					$stackitem->type = "FUNCTION";
					$stackitem->name = $this->_currentFunctionName;
				} else if ($this->_justAfterControlStmt) {
					// if _justAfterControlStmt is set, the "{" is the beginning of a control stratement block
					$this->_processControlStatementStart();
					$stackitem->type = strtoupper($this->_currentStatement);
				}  else if ($this->_inClassStatement) {
					// if _inClassStatement is set then we are just after a class declaration
					$this->_inClassStatement = false;
					$this->_processClassStart();
					$stackitem->type = "CLASS";
					$stackitem->name = $this->_currentClassname;
				}  else if ($this->_inInterfaceStatement) {
					// if _inInterfaceStatement is set then we are just after a Interface declaration
					$this->_inInterfaceStatement = false;
					$this->_processInterfaceStart();
					$stackitem->type = "INTERFACE";
					$stackitem->name = $this->_currentInterfacename;
				} else {
					$stackitem->type = "{";
				}

				// Check if the block is not empty
				$this->_checkEmptyBlock();

				array_push($this->_branchingStack, $stackitem);

				break;

			case "}":
				// "}" signifies the end of a block
				// currently tests whether this token resides on a new line.
				// This test is desactivated when in a view
				if ($this->_isActive('controlCloseCurly') && !($this->_isView) && (!$this->_inString)) {
					$previousTokenInfo = $this->tokenizer->peekPrvsValidToken();
					if ($previousTokenInfo->lineOffset == 0) {
						// the last token was on the same line
						$this->_writeError('controlCloseCurly', PHPCHECKSTYLE_END_BLOCK_NEW_LINE);
					}
				}

				$_currentStackItem = $this->_getCurrentStackItem();

				// FIXME: Add more robust handling of lines like:
				// $_fbTrackingCode = "FB|{$_ref}|{$_source}|{$_fbSource}|{$_notifType}|{$_fbBookmarkPos}";

				// Workaround code
				if (!Is_String($_currentStackItem)) {

					// Test for the end of a switch bloc
					if ($this->_getCurrentStackItem()->type == "SWITCH") {
						$this->_processSwitchStop();
					}

					// Test for the end of a function
					if ($this->_getCurrentStackItem()->type == "FUNCTION") {
						$this->_processFunctionStop();
					}

					// Test for the end of a class
					if ($this->_getCurrentStackItem()->type == "CLASS") {
						$this->_processClassStop();
					}

					// Test for the end of an interface
					if ($this->_getCurrentStackItem()->type == "INTERFACE") {
						$this->_processInterfaceStop();
					}

				}
				array_pop($this->_branchingStack);

				break;

			case ";":

				// ";" should never be preceded by a whitespace
				$this->_checkNoWhiteSpaceBefore($text);

				// ";" should never be preceded by ;
				$this->_checkEmptyStatement();

				break;

			case "-":
				if (!$this->_inFuncCall) {
					$this->_checkWhiteSpaceBefore($text);
				}
				// We allow some '-' signs to skip the the space afterwards for negative numbers
				if (!($this->tokenizer->checkNextToken(T_LNUMBER) || // float number
				$this->tokenizer->checkNextToken(T_DNUMBER))) {
					// integer
					$this->_checkWhiteSpaceAfter($text);
				}

				break;
			case "=":
				$this->_checkInnerAssignment();
				$this->_checkSurroundingWhiteSpace($text);
				break;
			case "<":
			case ">":
			case "+":
			case ".":
			case "*":
			case "/":
			case "?":
			case "==":
			case ":":
			case "%":
				// operators generally will need to be surrounded by whitespaces
				$this->_checkSurroundingWhiteSpace($text);
				break;

			case ",":
				$this->_checkNoWhiteSpaceBefore($text);
				$this->_checkWhiteSpaceAfter($text);
				break;

			case "!":
				$this->_checkNoWhiteSpaceAfter($text);
				break;

			case "(":
				// the only issue with "(" is generally whether there should be space after it or not
				if ($this->_inFuncCall) {
					// inside a function call
					$this->_fcLeftParenthesis += 1;
				} elseif ($this->_inControlStatement || $this->_inFunctionStatement) {
					// inside a function or control statement
					$this->_csLeftParenthesis += 1;
				}

				$this->_checkNoWhiteSpaceAfter($text);
				break;

			case ")":
				// Decrease the number of opened brackets
				if ($this->_inFuncCall) {
					$this->_fcLeftParenthesis -= 1;
				} elseif ($this->_inControlStatement || $this->_inFunctionStatement) {
					$this->_csLeftParenthesis -= 1;
				}

				// If 0 we are not in the call anymore
				if ($this->_fcLeftParenthesis == 0) {
					$this->_inFuncCall = false;
				}
				// If 0 we are not in the statement anymore
				if ($this->_csLeftParenthesis == 0) {

					if ($this->_inControlStatement) {
						$this->_inControlStatement = false;
						$this->_justAfterControlStmt = true;
						$this->_checkNeedBraces();
					} elseif ($this->_inFunctionStatement && !$this->_inInterface) {
						$this->_inFunctionStatement = false;
						$this->_justAfterFuncStmt = true;
					}
				}

				$this->_checkNoWhiteSpaceBefore($text);

				break;

			case "&":
				// One of the function parameter is passed by reference
				if ($this->_isActive('avoidPassingReferences')) {
					if ($this->_inFunctionStatement) {
						$this->_writeError('avoidPassingReferences', PHPCHECKSTYLE_PASSING_REFERENCE);
					}
				}
				break;
			case "[":
				$this->_inArrayStatement = true;
				break;
			case "]":
				$this->_inArrayStatement = false;
				break;

			case "'":
				$this->_trackStringOpenOrClose("'");
				break;

			case '"':
				$this->_trackStringOpenOrClose('"');
				break;

			default:
				break;
		}
	}

	/**
	 * processes a token that is not a string, that is
	 * a token that is a array of a token id (key) and
	 * a token text (value).
	 * @see http://www.php.net/manual/en/tokens.php
	 *
	 * @param $text the text of the token
	 * @param $tok token id
	 * @return nothing
	 * @access private
	 */
	private function _processToken($text, $tok) {

		// Debug
		if (DEBUG) {
			echo count($this->_branchingStack);
			echo "-".$this->tokenizer->getCurrentPosition();
			echo " Line  : ".$this->lineNumber;
			echo " Token ".$this->tokenizer->getTokenName($tok);
			echo " : ".$text.PHP_EOL;
			$this->_dumpStack();
		}

		// Check if the token is in the list of prohibited tokens
		if ($this->_isActive('checkProhibitedTokens') == 1) {
			foreach ($this->_prohibitedTokens as $prohibitedTokens) {
				if ($this->tokenizer->getTokenName($tok) == $prohibitedTokens) {
					$msg = sprintf(PHPCHECKSTYLE_PROHIBITED_TOKEN, $this->tokenizer->getTokenName($tok));
					$this->_writeError('checkProhibitedTokens', $msg);
				}
			}
		}

		switch ($tok) {
			case T_COMMENT:
			case T_ML_COMMENT:
			case T_DOC_COMMENT:
				$this->_processComment($tok, $text);
				break;

			case T_OPEN_TAG:
				// check if shorthand code tags are allowed
				if ($this->_isActive('noShortPhpCodeTag')) {
					$s = strpos($text, '<?php');
					if ($s === false) {
						$this->_writeError('noShortPhpCodeTag', PHPCHECKSTYLE_WRONG_OPEN_TAG);
					}
				}
				break;

				// Beginning of a control statement
			case T_DO:
			case T_WHILE:
			case T_IF:
			case T_ELSEIF:
			case T_FOR:
			case T_FOREACH:
				$this->_processControlStatement($text);
				$this->_cyclomaticComplexity++;
				break;
			case T_SWITCH:
				$this->_processSwitchStart();
				$this->_processControlStatement($text);
				$this->_cyclomaticComplexity++;
				break;
			case T_ELSE:
				// We don't increment the cyclomatic complexity for the last else
				$this->_processControlStatement($text);
				break;
			case T_CASE:
				$this->_processSwitchCase();
				$this->_cyclomaticComplexity++;
				break;
			case T_DEFAULT:
				$this->_processSwitchDefault();
				break;
			case T_BREAK:
				$this->_processSwitchBreak();
				break;
			case T_TRY:
				$this->_processControlStatement($text);
				break;
			case T_CATCH:
				$this->_processControlStatement($text);
				break;
			case T_WHITESPACE:
			case T_TAB :
				{
					if ($this->_isLineStart) {
						// If the whitespace is at the start of the line, we check for indentation
						$this->_checkIndentation($text);
					}
					break;
				}

			case T_INLINE_HTML:
				break;

				// beginning of a function definition
				// check also for existance of docblock
			case T_FUNCTION:
				$this->_checkDocExists(T_FUNCTION);
				$this->_processFunctionStatement();
				break;

				// beginning of a class
				// check also for the existence of a docblock
			case T_CLASS:
				$this->_checkDocExists(T_CLASS);
				$this->_processClassStatement();
				break;

				// beginning of an interface
				// check also for the existence of a docblock
			case T_INTERFACE:
				$this->_checkDocExists(T_INTERFACE);
				$this->_processInterfaceStatement();
				break;

				// operators, generally, need to be surrounded by whitespace
			case T_PLUS_EQUAL:
			case T_MINUS_EQUAL:
			case T_MUL_EQUAL:
			case T_DIV_EQUAL:
			case T_CONCAT_EQUAL:
			case T_MOD_EQUAL:
			case T_AND_EQUAL:
			case T_OR_EQUAL:
			case T_XOR_EQUAL:
			case T_SL_EQUAL:
			case T_SR_EQUAL:
			case T_BOOLEAN_OR:
			case T_BOOLEAN_AND:
			case T_IS_EQUAL:
			case T_IS_NOT_EQUAL:
			case T_IS_IDENTICAL:
			case T_IS_NOT_IDENTICAL:
			case T_IS_SMALLER_OR_EQUAL:
			case T_IS_GREATER_OR_EQUAL:
				$this->_checkSurroundingWhiteSpace($text);
				break;
			case T_LOGICAL_AND:
			case T_LOGICAL_OR:
				if ($this->_isActive('useBooleanOperators')) {
					$this->_writeError('useBooleanOperators', PHPCHECKSTYLE_USE_BOOLEAN_OPERATORS);
				}
				$this->_checkSurroundingWhiteSpace($text);
				break;

				// ASSUMPTION:
				//   that T_STRING followed by "(" is a function call
				//   Actually, I am not sure how good an assumption this is.
			case T_STRING:

				// If the word "define" have been used right before the string
				if ($this->_constantDef == true) {
					$this->_checkConstantNaming($this->token[1]);
				}

				// Check whether this is a function call (and if "define", set the flag)
				$this->_processFunctionCall($text);

				break;

				// found constant definition
			case T_CONST:
				// Skip until T_STRING representing the constant name
				while (!$this->tokenizer->checkProvidedToken($this->token, T_STRING)) {
					$this->_moveToken();
				}
				$this->_constantDef = true;
				$this->_checkConstantNaming($this->token[1]);
				break;

			case T_CONSTANT_ENCAPSED_STRING:

				// If the word "define" have been used right before the constant encapsed string
				if ($this->_constantDef == true) {
					$this->_checkConstantNaming($this->token[1]);
				}

				// Manage new lines inside string
				$subToken = strtok($text, PHP_EOL);
				while ($subToken !== false) {
					// Increment the lines number (one comment is only one token)
					$this->lineNumber++;
					$subToken = strtok(PHP_EOL);
				}
				$this->lineNumber--; // One end of line is already counted

				break;


			case T_ENCAPSED_AND_WHITESPACE:
				// Constant part of string with variables
				$this->_checkEncapsedVariablesInsideString();
				break;
			case T_CURLY_OPEN: // for protected variables within strings "{$var}"
				array_push($this->_branchingStack, 'curly_open');
				break;
			case T_DOLLAR_OPEN_CURLY_BRACES: // for extended format "${var}"
				array_push($this->_branchingStack, 'dollar_curly_open');
				break;
			case T_NEW_LINE:
				$this->_countLinesOfCode();
				$this->lineNumber++;

				if ($this->_displayProgress) {
					echo "File: ".$this->_currentFilename." | Line: ".$this->lineNumber."\n";
				}
				// Case of a control statement without parenthesis, it closes at the end of the line
				if ($this->_inControlStatement && $this->_csLeftParenthesis == 0) {
					$this->_inControlStatement = false;
				}
				// Test the length of the line, only if it's not html.
				if ($this->_isActive('lineLength')) {
					$this->_checkLargeLine();
				}
				break;
			case T_RETURN:
				$this->_processReturn();
				break;
			case T_THROW:
				$this->_processThrow();
				break;
			case T_INC:
			case T_DEC:
				$this->_checkUnaryOperator();
				break;
			case T_DOUBLE_ARROW:
				$this->_checkSurroundingWhiteSpace($text);
				break;
			case T_OBJECT_OPERATOR:
				$this->_checkNoWhiteSpaceBefore($text);
				$this->_checkNoWhiteSpaceAfter($text);
				break;
			case T_START_HEREDOC:
				$this->_processStartHeredoc();
				break;
			case T_END_HEREDOC:
				$this->_processEndHeredoc();
				break;
			case T_VARIABLE:
				$this->_processVariable($text);
				break;
			case T_GOTO:
				$this->_checkGoTo();
				break;
			case T_CONTINUE:
				$this->_checkContinue();
				break;
			default:
				break;
		}

		// If the last token is a NEW_LINE, the next token will be at the start of the line
		$this->_isLineStart = ($tok == T_NEW_LINE);
	}

	/**
	 * Check if the current line if a line of code and if it's the case increment the count.
	 *
	 * This function is called when we meet a T_NEW_LINE token.
	 */
	private function _countLinesOfCode() {

		// We get the previous token (T_WHITESPACE, T_COMMENT, ... ignored);
		$previousTokenInfo = $this->tokenizer->peekPrvsValidToken();

		// If the previous token is not the new line (empty line), we suppose we have some code
		if ($previousTokenInfo != null) {
			$previousToken = $previousTokenInfo->token;
			if (!$this->tokenizer->checkProvidedToken($previousToken, T_NEW_LINE) && $previousTokenInfo->lineOffset == 0) {
				$this->_ncssTotalLinesOfCode++;
				$this->_ncssFileLinesOfCode++;
			}
		}

	}

	/**
	 * Checks to see if the constant follows the naming convention
	 * Constants should only have uppercase letters and underscores
	 *
	 * @param String $text the string containing the constant. Note that the
	 *        string also has the quotes (single or double), so we need
	 *        remove them from the string before testing
	 */
	private function _checkConstantNaming($text) {
		if ($this->_isActive('constantNaming')) {
			$text = ltrim($text, "\"'");  // just in case, remove the quotes
			$text = rtrim($text, "\"'");
			$ret = preg_match($this->_config->getTestRegExp('constantNaming'), $text);
			if (!$ret) {
				$msg = sprintf(PHPCHECKSTYLE_CONSTANT_NAMING, $text, $this->_config->getTestRegExp('constantNaming'));
				$this->_writeError('constantNaming', $msg);
			}
		}
		$this->_constantDef = false;
	}

	/**
	 * Checks to see if the variable follows the naming convention
	 * Variables should only have letters, start with a lowercase and have no underscore.
	 *
	 * @param String $text the string containing the variable. note that the
	 *        string also has the quotes (single or double), so we need
	 *        remove them from the string before testing
	 */
	private function _checkVariableNaming($text) {

		if ($this->_inClass || $this->_inInterface) {
			if ($this->_inFunction || $this->_inFunctionStatement || $this->_inInterfaceStatement) {
				$this->_checkScopedVariableNaming($text, 'localVariableNaming', PHPCHECKSTYLE_LOCAL_VARIABLE_NAMING);
			} else {
				$this->_checkScopedVariableNaming($text, 'memberVariableNaming', PHPCHECKSTYLE_MEMBER_VARIABLE_NAMING);
			}
		} else {
			$this->_checkScopedVariableNaming($text, 'topLevelVariableNaming', PHPCHECKSTYLE_TOPLEVEL_VARIABLE_NAMING);
		}
	}

	/**
	 * Utility function to check the naming of a variable
	 * given its scope rule and message.
	 *
	 * @param String $text the string containing the variable. note that the
	 *        				string also has the quotes (single or double), so
	 *        				we need to remove them from the string before
	 *        				testing
	 * @param String $ruleName the rule for the scope of the variable
	 * @param String $msgName the message associated with the rule
	 */
	private function _checkScopedVariableNaming($variableText, $ruleName, $msgName) {
		if ($this->_isActive($ruleName) || $this->_isActive('variableNaming')) {
			$texttoTest = ltrim($variableText, "\"'"); // remove the quotes
			$texttoTest = rtrim($texttoTest, "\"'");
			if (strpos($texttoTest, "$") === 0) {
				// remove the "&"
				$texttoTest = substr($texttoTest, 1);
			}
			// If the variable is not listed as an exception
			$exceptions = $this->_config->getTestExceptions($ruleName);
			if (empty($exceptions) || !in_array($text, $exceptions)) {

				if ($this->_isActive($ruleName)) {
					// Scoped variable
					$ret = preg_match($this->_config->getTestRegExp($ruleName), $texttoTest);
				} else {
					// Default case
					$ret = preg_match($this->_config->getTestRegExp('variableNaming'), $texttoTest);
				}
				if (!$ret) {
					if ($this->_isActive($ruleName)) {
						$msg = sprintf($msgName, $variableText, $this->_config->getTestRegExp($ruleName));
					} else {
						$msg = sprintf(PHPCHECKSTYLE_VARIABLE_NAMING, $variableText, $this->_config->getTestRegExp('variableNaming'));
					}
					$this->_writeError($ruleName, $msg);
				}
			}
		}
	}


	/**
	 * Check the naming of a function.
	 *
	 * @param String $text the name of the function.
	 */
	private function _checkFunctionNaming($text) {
		if ($this->_isActive('functionNaming')) {

			$ret = preg_match($this->_config->getTestRegExp('functionNaming'), $text);
			if (!$ret) {
				$msg = sprintf(PHPCHECKSTYLE_FUNCNAME_NAMING, $text, $this->_config->getTestRegExp('functionNaming'));
				$this->_writeError('functionNaming', $msg);
			}
		}
	}

	/**
	 * Check the naming of a private function.
	 *
	 * @param String $text the name of the function.
	 */
	private function _checkPrivateFunctionNaming($text) {

		if ($this->_isActive('privateFunctionNaming')) {
			$ret = preg_match($this->_config->getTestRegExp('privateFunctionNaming'), $text);
			if (!$ret) {
				$msg = sprintf(PHPCHECKSTYLE_PRIVATE_FUNCNAME_NAMING, $text, $this->_config->getTestRegExp('privateFunctionNaming'));
				$this->_writeError('privateFunctionNaming', $msg);
			}
		}
	}

	/**
	 * Check the naming of a protected function.
	 *
	 * @param String $text the name of the function.
	 */
	private function _checkProtectedFunctionNaming($text) {
		if ($this->_isActive('protectedFunctionNaming')) {
			$ret = preg_match($this->_config->getTestRegExp('protectedFunctionNaming'), $text);
			if (!$ret) {
				$msg = sprintf(PHPCHECKSTYLE_PROTECTED_FUNCNAME_NAMING, $text, $this->_config->getTestRegExp('protectedFunctionNaming'));
				$this->_writeError('protectedFunctionNaming', $msg);
			}
		}
	}

	/**
	 * Check the naming of a class.
	 *
	 * @param String $text the name of the class.
	 */
	private function _checkClassNaming($text) {
		if ($this->_isActive('classNaming')) {
			$ret = preg_match($this->_config->getTestRegExp('classNaming'), $text);
			if (!$ret) {
				$msg = sprintf(PHPCHECKSTYLE_CLASSNAME_NAMING, $text, $this->_config->getTestRegExp('classNaming'));
				$this->_writeError('classNaming', $msg);
			}
		}
	}

	/**
	 * Check the naming of an interface.
	 *
	 * @param String $text the name of the interface.
	 */
	private function _checkInterfaceNaming($text) {

		if ($this->_isActive('interfaceNaming')) {
			$ret = preg_match($this->_config->getTestRegExp('interfaceNaming'), $text);
			if (!$ret) {
				$msg = sprintf(PHPCHECKSTYLE_INTERFACENAME_NAMING, $text, $this->_config->getTestRegExp('interfaceNaming'));
				$this->_writeError('interfaceNaming', $msg);
			}
		}
	}

	/**
	 * Check the naming of a file.
	 */
	private function _checkFileNaming() {
		if ($this->_isActive('fileNaming')) {
			$fileBaseName = basename($this->_currentFilename);

			$ret = preg_match($this->_config->getTestRegExp('fileNaming'), $fileBaseName);
			if (!$ret) {
				$msg = sprintf(PHPCHECKSTYLE_FILENAME_NAMING, $fileBaseName, $this->_config->getTestRegExp('fileNaming'));
				$this->_writeError('fileNaming', $msg);
			}
		}
	}

	/**
	 * Check that the type name matches the file name.
	 *
	 * @param String $typeName the name of the type.
	 */
	private function _checkTypeNameFileNameMatch($typeName) {
		/* currentFilename holds file path - get basename */
		$fileBaseName = basename($this->_currentFilename);
		/* quick n dirty - append '.' to type name to ensure that
		 * we don't miss something like Foo1.php and Foo
		*/
		if ($this->_isActive('typeNameMatchesFileName') && !(substr($fileBaseName, 0, strlen($typeName) + 1) === $typeName . ".")) {
			$msg = sprintf(PHPCHECKSTYLE_TYPE_FILE_NAME_MISMATCH, $typeName, $fileBaseName);
			$this->_writeError('typeNameMatchesFileName', $msg);
		}
	}

	/**
	 * Check the validity of a function call.
	 *
	 * @param String $text the name of the function.
	 */
	private function _processFunctionCall($text) {

		if ($text == "define") {
			$this->_constantDef = true;
		}

		if ($this->tokenizer->checkNextValidTextToken("(")) {
			// ASSUMPTION:that T_STRING followed by "(" is a function call
			$this->_inFuncCall = true;

			// Add the function name to the list of used functions
			$this->_usedFunctions[$text] = $text;

			// Detect prohibited functions
			if ($this->_isActive('checkProhibitedFunctions')) {
				if (in_array($text, $this->_prohibitedFunctions)) {
					$msg = sprintf(PHPCHECKSTYLE_PROHIBITED_FUNCTION, $text);
					$this->_writeError('checkProhibitedFunctions', $msg);
				}
			}

			// Detect deprecated functions
			$this->_checkDeprecation($text);

			// Detect an @ before the function call
			$this->_checkSilenced($text);

			// Detect space after function name
			if ($this->_isActive('noSpaceAfterFunctionName')) {
				if (!$this->tokenizer->checkNextTextToken("(")) {
					$msg = sprintf(PHPCHECKSTYLE_NO_SPACE_AFTER_TOKEN, $text);
					$this->_writeError('noSpaceAfterFunctionName', $msg);
				}
			}
		}

		// Optimisation : Avoid using count/sizeof inside a loop
		if ($this->_isActive('functionInsideLoop')) {
			if ((strtolower($text) == 'count' || strtolower($text) == 'sizeof') && $this->_inControlStatement) {
				if ($this->_currentStatement == 'do' || $this->_currentStatement == 'while' || $this->_currentStatement == 'for' || $this->_currentStatement == 'foreach') {
					$msg = sprintf(PHPCHECKSTYLE_FUNCTION_INSIDE_LOOP, strtolower($text));
					$this->_writeError('functionInsideLoop', $msg);
				}
			}
		}
	}

	/**
	 * Process a control statement declaration (if/do/while/for/...).
	 *
	 * @param String $csText the control statement.
	 */
	private function _processControlStatement($csText) {

		$csText = strtolower($csText);

		$this->_inControlStatement = true;
		$this->_currentStatement = $csText;

		// first token: if not one whitespace, error
		if ($this->_isActive('spaceAfterControlStmt')) {
			if (!$this->tokenizer->checkNextToken(T_WHITESPACE)) {
				if ($csText != 'else' && $csText != 'try' && $csText != 'do') {
					$msg = sprintf(PHPCHECKSTYLE_SPACE_AFTER_TOKEN, $csText);
					$this->_writeError('spaceAfterControlStmt', $msg);
				}
			}
		}

		// for some control structures like "else" and "do",
		// there is no statements they will be followed directly by "{"
		if ($csText == "else" || $csText == "do" || $csText == "try") {
			if ($this->tokenizer->checkNextValidTextToken("{")) {
				$this->_inControlStatement = false;
				$this->_justAfterControlStmt = true;
			}
		}

		// "else if" is different
		if ($csText == "else") {
			if ($this->tokenizer->checkNextValidToken(T_IF)) {
				// control statement for "else" is done with... new control
				// statement "if" is starting
				$this->_inControlStatement = false;
			}
		}

		// "else" and "elseif" should start in the same line as "}"
		if ($csText == "else" || $csText == "elseif") {
			$position = $this->_config->getTestProperty('controlStructElse', 'position');
			$previousTokenInfo = $this->tokenizer->peekPrvsValidToken();
			if (($position == 'sl') && ($previousTokenInfo->lineOffset != 0)) {
				$msg = sprintf(PHPCHECKSTYLE_CS_STMT_ALIGNED_WITH_CURLY, $csText);
				$this->_writeError('controlStructElse', $msg);
			}
			if (($position == 'nl') && ($previousTokenInfo->lineOffset == 0)) {
				$msg = sprintf(PHPCHECKSTYLE_CS_STMT_ON_NEW_LINE, $csText);
				$this->_writeError('controlStructElse', $msg);
			}
		}

		// To avoid a false positive when treating the while statement of a do/while
		// We keep track that we have met a do statement
		if ($csText == "do") {
			// Note : The current stack item is not yet the control statement itself, it's the parent
			$this->_getCurrentStackItem()->afterDoStatement = true;
		}
	}

	/**
	 * Process the start of a control structure (if/do/while/for/...).
	 *
	 * Launched when we meet the { just after the statement declaration.
	 */
	private function _processControlStatementStart() {

		// check for curly braces
		if ($this->_isActive('controlStructOpenCurly')) {

			$pos = $this->_config->getTestProperty('controlStructOpenCurly', 'position');

			$previousTokenInfo = $this->tokenizer->peekPrvsValidToken();

			if ($pos == "nl") {
				// We expect the next token after the curly to be on a new line
				$isPosOk = ($previousTokenInfo->lineOffset < 0);
			} else {
				// We expect the next token after the curly to be on the same line
				$isPosOk = ($previousTokenInfo->lineOffset == 0);
			}

			if (!$isPosOk) {
				$tmp = ($pos == "sl") ? "the previous line." : "a new line.";
				$msg = sprintf(PHPCHECKSTYLE_LEFT_CURLY_POS, $tmp);
				$this->_writeError('controlStructOpenCurly', $msg);
			}
		}

		// WARN: used for a very simple (and wrong!) do/while processing
		$this->_justAfterControlStmt = false;
	}

	/**
	 * Process the start of a interface.
	 */
	private function _processInterfaceStart() {
		$this->_inInterface = true;
		$this->_interfaceLevel = count($this->_branchingStack);

		// Check the position of the open curly after the interface declaration
		if ($this->_isActive('interfaceOpenCurly')) {
			$pos = $this->_config->getTestProperty('interfaceOpenCurly', 'position');

			$previousTokenInfo = $this->tokenizer->peekPrvsValidToken();

			if ($pos == "nl") {
				// The previous token should be on the previous line
				$isPosOk = ($previousTokenInfo->lineOffset < 0);
			} else {
				// The previous token should be on the same line
				$isPosOk = ($previousTokenInfo->lineOffset == 0);
			}

			if (!$isPosOk) {
				$tmp = ($pos == "sl") ? "the previous line." : "a new line.";
				$msg = sprintf(PHPCHECKSTYLE_LEFT_CURLY_POS, $tmp);
				$this->_writeError('interfaceOpenCurly', $msg);
			}
		}
	}

	/**
	 * Process the end of a interface.
	 */
	private function _processInterfaceStop() {
		// We are out of the interface
		$this->_inInterface = false;
	}

	/**
	 * Process the start of a class.
	 */
	private function _processClassStart() {
		$this->_inClass = true;
		$this->_classLevel = count($this->_branchingStack);

		// Check the position of the open curly after the class declaration
		if ($this->_isActive('classOpenCurly')) {
			$pos = $this->_config->getTestProperty('classOpenCurly', 'position');

			$previousTokenInfo = $this->tokenizer->peekPrvsValidToken();

			if ($pos == "nl") {
				// The previous token should be on the previous line
				$isPosOk = ($previousTokenInfo->lineOffset < 0);
			} else {
				// The previous token should be on the same line
				$isPosOk = ($previousTokenInfo->lineOffset == 0);
			}

			if (!$isPosOk) {
				$tmp = ($pos == "sl") ? "the previous line." : "a new line.";
				$msg = sprintf(PHPCHECKSTYLE_LEFT_CURLY_POS, $tmp);
				$this->_writeError('classOpenCurly', $msg);
			}
		}
	}

	/**
	 * Process the end of a class.
	 */
	private function _processClassStop() {
		// We are out of the class
		$this->_inClass = false;

		// Reset of the warnings suppression is done at the end of the file, hoping we have 1 file / class
	}

	/**
	 * Process the start of a function.
	 */
	private function _processFunctionStart() {

		$this->_inFunction = true;
		$this->_cyclomaticComplexity = 1;
		$this->_functionLevel = count($this->_branchingStack);
		$this->_justAfterFuncStmt = false;

		$this->_functionStartLine = $this->lineNumber;

		// Check the position of the open curly after the function declaration
		if ($this->_isActive('funcDefinitionOpenCurly')) {
			$pos = $this->_config->getTestProperty('funcDefinitionOpenCurly', 'position');

			$previousTokenInfo = $this->tokenizer->peekPrvsValidToken();

			if ($pos == "nl") {
				// The previous token should be on the previous line
				$isPosOk = ($previousTokenInfo->lineOffset < 0);
			} else {
				// The previous token should be on the same line
				$isPosOk = ($previousTokenInfo->lineOffset == 0);
			}

			if (!$isPosOk) {
				$tmp = ($pos == "sl") ? "the previous line." : "a new line.";
				$msg = sprintf(PHPCHECKSTYLE_LEFT_CURLY_POS, $tmp);
				$this->_writeError('funcDefinitionOpenCurly', $msg);
			}
		}
	}

	/**
	 * Process the end of a function declaration.
	 */
	private function _processFunctionStop() {

		$this->_inFunction = false; // We are out of the function

		// Check cyclomaticComplexity
		if ($this->_isActive('cyclomaticComplexity')) {

			$warningLevel = $this->_config->getTestProperty('cyclomaticComplexity', 'warningLevel');
			$errorLevel = $this->_config->getTestProperty('cyclomaticComplexity', 'errorLevel');
			$msg = sprintf(PHPCHECKSTYLE_CYCLOMATIC_COMPLEXITY, $this->_currentFunctionName, $this->_cyclomaticComplexity, $warningLevel);

			//Direct call to the reporter to allow different error levels for a single test.
			if ($this->_cyclomaticComplexity > $warningLevel) {
				$this->_reporter->writeError($this->_functionStartLine, 'cyclomaticComplexity', $msg, 'WARNING');
			} else if ($this->_cyclomaticComplexity > $errorLevel) {
				$this->_reporter->writeError($this->_functionStartLine, 'cyclomaticComplexity', $msg, 'ERROR');
			}
		}

		//
		// Check that the declared parameters in the docblock match the content of the function
		//
		// If the function is not private and we check the doc
		$isPrivateExcluded = $this->_config->getTestProperty('docBlocks', 'excludePrivateMembers');
		if (!($isPrivateExcluded && $this->_functionVisibility == 'PRIVATE')) {

			// Check the docblock @return
			if ($this->_isActive('docBlocks') && ($this->_config->getTestProperty('docBlocks', 'testReturn') != 'false')) {

				if ($this->_functionReturns && ($this->_docblocNbReturns == 0)) {
					$msg = sprintf(PHPCHECKSTYLE_DOCBLOCK_RETURN, $this->_currentFunctionName);
					$this->_writeError('docBlocks', $msg);
				}
			}

			// Check the docblock @param
			if ($this->_isActive('docBlocks') && ($this->_config->getTestProperty('docBlocks', 'testParam') != 'false')) {
				if ($this->_nbFunctionParameters != $this->_docblocNbParams) {
					$msg = sprintf(PHPCHECKSTYLE_DOCBLOCK_PARAM, $this->_currentFunctionName);
					$this->_writeError('docBlocks', $msg);
				}
			}

			// Check the docblock @throw
			if ($this->_isActive('docBlocks') && ($this->_config->getTestProperty('docBlocks', 'testThrow') != 'false')) {
					

				if ($this->_functionThrows && ($this->_docblocNbThrows == 0)) {
					$msg = sprintf(PHPCHECKSTYLE_DOCBLOCK_THROW, $this->_currentFunctionName);
					$this->_writeError('docBlocks', $msg);
				}
			}
		}

		$this->_docblocNbParams = 0;
		$this->_docblocNbReturns = 0;
		$this->_docblocNbThrows = 0;

		// Check the length of the function
		if ($this->_isActive('functionLength')) {

			$functionLength = $this->lineNumber - $this->_functionStartLine;
			$maxLength = $this->_config->getTestProperty('functionLength', 'maxLength');

			if ($functionLength > $maxLength) {
				$msg = sprintf(PHPCHECKSTYLE_FUNCTION_LENGTH_THROW, $this->_currentFunctionName, $functionLength, $maxLength);
				$this->_writeError('docBlocks', $msg);
			}
		}

		// Check unused function parameters
		$this->_checkUnusedFunctionParameters();

		$this->_functionSuppressWarnings = array(); // Reset the warnings suppressed

	}

	/**
	 * Process a function declaration statement (the parameters).
	 */
	private function _processFunctionStatement() {

		// Increment the number of functions
		$this->_ncssTotalFunctions++;
		$this->_ncssFileFunctions++;

		// Reset the default values
		$this->funcArgString = "";
		$this->_nbFunctionParameters = 0;
		$this->_functionParameters = array();
		$this->_inFunctionStatement = true;
		$this->_functionReturns = false;
		$this->_functionThrows = false;
		$this->_inControlStatement = false;
		$this->_currentStatement = false;
		$this->_inClassStatement = false;
		$this->_inInterfaceStatement = false;

		// Detect the function visibility
		$this->_functionVisibility = 'PUBLIC';
		if ($this->tokenizer->checkPreviousValidToken(T_PRIVATE)) {
			$this->_functionVisibility = 'PRIVATE';
		} else if ($this->tokenizer->checkPreviousValidToken(T_PROTECTED)) {
			$this->_functionVisibility = 'PROTECTED';
		}

		// Skip until T_STRING representing the function name
		while (!$this->tokenizer->checkProvidedToken($this->token, T_STRING)) {
			$this->_moveToken();
		}
		// Tracking the function's name.
		$functionName = $this->token[1];
		$this->_currentFunctionName = $functionName;

		// If the function is private we add it to the list of function to use (and store the line number)
		if ($this->_functionVisibility == 'PRIVATE') {
			$this->_privateFunctions[$functionName] = $functionName;
			$this->_privateFunctionsStartLines[$functionName] = $this->lineNumber;
		}

		// Function is a constructor
		if ($functionName == "__construct" && $this->_isActive('constructorNaming') && $this->_config->getTestProperty('constructorNaming', 'naming') == 'old') {
			$msg = sprintf(PHPCHECKSTYLE_CONSTRUCTOR_NAMING, $this->_currentClassname);

			$this->_writeError('constructorNaming', $msg);
		}

		// Special functions are not checked
		if (!in_array($functionName, $this->_specialFunctions)) {

			// Constructors
			if ($functionName == $this->_currentClassname) {

				// Function is a constructor
				if ($this->_isActive('constructorNaming') && $this->_config->getTestProperty('constructorNaming', 'naming') == 'new') {
					$msg = sprintf(PHPCHECKSTYLE_CONSTRUCTOR_NAMING, '__construct()');

					$this->_writeError('constructorNaming', $msg);
				}

			} else {

				// Other funnction
				if ($this->_functionVisibility == 'PRIVATE') {
					$this->_checkPrivateFunctionNaming($this->_currentFunctionName);
				} else if ($this->_functionVisibility == 'PROTECTED') {
					$this->_checkProtectedFunctionNaming($this->_currentFunctionName);
				} else {
					$this->_checkFunctionNaming($this->_currentFunctionName);
				}
			}

		}

		// List the arguments of the currently analyzed function.
		// Check the order of the parameters of the function.
		// The parameters having a default value should be in last position.
		$foundDefaultValues = false;
		$functionTokenPosition = $this->tokenizer->getCurrentPosition();
		while (true) {
			$functionToken = $this->tokenizer->peekTokenAt($functionTokenPosition);

			if ($this->tokenizer->checkProvidedText($functionToken, ')')) {
				// We found the closing brace
				break;
			}

			// If we find a "=" we consided that the parameter has a default value
			$defaultSpecified = $this->tokenizer->checkProvidedText($functionToken, "=");
			// We have found one default value for at least one parameter
			if ($defaultSpecified) {
				$foundDefaultValues = true;
			}

			// Current token is a parameter
			if ($this->tokenizer->checkProvidedToken($functionToken, T_VARIABLE)) {
				$this->_nbFunctionParameters++;
				$parameterName = $this->tokenizer->extractTokenText($functionToken);
				$this->_functionParameters[$parameterName] = "unused"; // We flag the parameter as unused

				// Check is this parameter as a default value
				$nextTokenInfo = $this->tokenizer->peekNextValidToken($functionTokenPosition + 1);
				$hasDefaultValue = $this->tokenizer->checkProvidedText($nextTokenInfo->token, "=");

				// Check if the parameter has a default value
				if ($this->_isActive('defaultValuesOrder')) {
					if ($foundDefaultValues && !$hasDefaultValue) {
						$this->_writeError('defaultValuesOrder', PHPCHECKSTYLE_FUNC_DEFAULTVALUE_ORDER);
						break;
					}
				}
			}

			$functionTokenPosition++;

		}

		// Test for the max number of parameters
		if ($this->_isActive('functionMaxParameters')) {
			$paramCount = $this->_nbFunctionParameters;
			$maxParams = $this->_config->getTestProperty('functionMaxParameters', 'maxParameters');
			if ($paramCount > $maxParams) {
				$msg = sprintf(PHPCHECKSTYLE_MAX_PARAMETERS, $this->_currentFunctionName, $paramCount, $maxParams);
				$this->_writeError('functionMaxParameters', $msg);
			}
		}
	}

	/**
	 * Process the start of a switch block.
	 */
	private function _processSwitchStart() {
		$this->_inSwitch = true;
		$this->_switchStartLine = $this->lineNumber;
	}

	/**
	 * Process the end of a switch block.
	 */
	private function _processSwitchStop() {
		$this->_inSwitch = false;
		$this->_checkSwitchNeedDefault();
	}

	/**
	 * Check if the default case of a switch statement is present.
	 *
	 * This function is launched at the end of switch/case.
	 */
	private function _checkSwitchNeedDefault() {

		if ($this->_isActive('switchNeedDefault')) {
			if (!$this->_getCurrentStackItem()->switchHasDefault) {
				// Direct call to reporter to include a custom line number.
				$this->_reporter->writeError($this->_switchStartLine, 'switchNeedDefault', PHPCHECKSTYLE_SWITCH_DEFAULT, $this->_config->getTestLevel('switchNeedDefault'));
			}
		}
	}

	/**
	 * Process a case statement.
	 */
	private function _processSwitchCase() {

		// Test if the previous case had a break
		$this->_checkSwitchCaseNeedBreak();

		// If the case arrives after the default
		$this->_checkSwitchDefaultOrder();

		// For this case
		$this->_getCurrentStackItem()->caseHasBreak = false;
		$this->_getCurrentStackItem()->caseStartLine = $this->lineNumber;
	}

	/**
	 * Check if the switch/case statement default case appear at the end.
	 *
	 * This function is launched at the start of each case.
	 */
	private function _checkSwitchDefaultOrder() {
		if ($this->_isActive('switchDefaultOrder')) {
			if ($this->_getCurrentStackItem()->switchHasDefault) {
				// The default flag is already set, it means that a previous case case a default
				$this->_writeError('switchDefaultOrder', PHPCHECKSTYLE_SWITCH_DEFAULT_ORDER);
			}
		}
	}

	/**
	 * Check if the case statement of a swtich/case a a break instruction.
	 *
	 * This function is launched at the start of each case.
	 */
	private function _checkSwitchCaseNeedBreak() {
		// Test if the previous case had a break
		if ($this->_isActive('switchCaseNeedBreak') && !$this->_getCurrentStackItem()->caseHasBreak) {
			// Direct call to reporter to include a custom line number.
			$this->_reporter->writeError($this->_getCurrentStackItem()->caseStartLine, 'switchCaseNeedBreak', PHPCHECKSTYLE_SWITCH_CASE_NEED_BREAK, $this->_config->getTestLevel('switchCaseNeedBreak'));
		}
	}

	/**
	 * Process a default statement.
	 */
	private function _processSwitchDefault() {
		$this->_getCurrentStackItem()->switchHasDefault = true;
	}

	/**
	 * Process a break statement.
	 */
	private function _processSwitchBreak() {
		$this->_getCurrentStackItem()->caseHasBreak = true;
	}

	/**
	 * Process an interface declaration statement.
	 */
	private function _processInterfaceStatement() {

		$this->_ncssTotalInterfaces++;
		$this->_ncssFileInterfaces++;

		// Test if there is more than one class per file
		if ($this->_isActive('oneInterfacePerFile') && $this->_ncssFileInterfaces > 1) {
			$msg = sprintf(PHPCHECKSTYLE_ONE_INTERFACE_PER_FILE, $this->_currentFilename);
			$this->_writeError('oneInterfacePerFile', $msg);
		}

		// Reset the default values
		$this->_inFunction = false;
		$this->_nbFunctionParameters = 0;
		$this->_functionParameters = array();
		$this->_inFunctionStatement = false;
		$this->_functionReturns = false;
		$this->_functionThrows = false;
		$this->_inControlStatement = false;
		$this->_currentStatement = false;
		$this->_inInterfaceStatement = true;

		// skip until T_STRING representing the interface name
		while (!$this->tokenizer->checkProvidedToken($this->token, T_STRING)) {
			$this->_moveToken();
		}

		$interfacename = $this->token[1];
		$this->_currentInterfacename = $interfacename;

		// Test that the interface name matches the file name
		$this->_checkTypeNameFileNameMatch($interfacename);

		// Check interface naming
		$this->_checkInterfaceNaming($interfacename);

		$this->_checkWhiteSpaceAfter($interfacename);
	}

	/**
	 * Process a class declaration statement.
	 */
	private function _processClassStatement() {

		$this->_ncssTotalClasses++;
		$this->_ncssFileClasses++;

		// Test if there is more than one class per file
		if ($this->_isActive('oneClassPerFile') && $this->_ncssFileClasses > 1) {
			$msg = sprintf(PHPCHECKSTYLE_ONE_CLASS_PER_FILE, $this->_currentFilename);
			$this->_writeError('oneClassPerFile', $msg);
		}

		// Reset the default values
		$this->_inFunction = false;
		$this->_nbFunctionParameters = 0;
		$this->_functionParameters = array();
		$this->_inFunctionStatement = false;
		$this->_functionReturns = false;
		$this->_functionThrows = false;
		$this->_inControlStatement = false;
		$this->_currentStatement = false;
		$this->_inClassStatement = true;

		// skip until T_STRING representing the class name
		while (!$this->tokenizer->checkProvidedToken($this->token, T_STRING)) {
			$this->_moveToken();
		}

		$classname = $this->token[1];
		$this->_currentClassname = $classname;

		// Test that the class name matches the file name
		$this->_checkTypeNameFileNameMatch($classname);

		// Check class naming
		$this->_checkClassNaming($classname);

		$this->_checkWhiteSpaceAfter($classname);
	}

	/**
	 * Check for empty block.
	 *
	 * This function is launched when the current token is {
	 */
	private function _checkEmptyBlock() {

		// If the next valid token is } then the statement is empty.
		if ($this->_isActive('checkEmptyBlock') && $this->_currentStatement) {

			if ($this->tokenizer->checkNextValidTextToken("}")) {
				$msg = sprintf(PHPCHECKSTYLE_EMPTY_BLOCK, $this->_currentStatement);
				$this->_writeError('checkEmptyBlock', $msg);
			}
		}
	}

	/**
	 * Check for encapsed variables inside string.
	 *
	 * This function is launched when the current token is T_ENCAPSED_AND_WHITESPACE.
	 */
	private function _checkEncapsedVariablesInsideString() {
		if ($this->_isActive('encapsedVariablesInsideString') && !$this->_getCurrentStackItem()->inHeredoc) {
			$this->_writeError('encapsedVariablesInsideString', PHPCHECKSTYLE_VARIABLE_INSIDE_STRING);
		}
	}

	/**
	 * Check for inner assignments.
	 *
	 * This function is launched when the current token is = (assignment).
	 */
	private function _checkInnerAssignment() {

		// If the test if active and we are inside a control statement
		if ($this->_isActive('checkInnerAssignment') && $this->_inControlStatement) {

			// If the control statement is not listed as an exception
			$exceptions = $this->_config->getTestExceptions('checkInnerAssignment');
			if (empty($exceptions) || !in_array($this->_currentStatement, $exceptions)) {
				$this->_writeError('checkInnerAssignment', PHPCHECKSTYLE_INSIDE_ASSIGNMENT);

			}
		}
	}

	/**
	 * Check for empty statement.
	 *
	 * This function is launched when the current token is ;
	 */
	private function _checkEmptyStatement() {

		// If the next valid token is ; then the statement is empty.
		if ($this->_isActive('checkEmptyStatement')) {

			if ($this->tokenizer->checkNextValidTextToken(";")) {
				$this->_writeError('checkEmptyStatement', PHPCHECKSTYLE_EMPTY_STATEMENT);
			}
		}
	}

	/**
	 * Check for unused functions.
	 *
	 * This function is launched at the end of a file.
	 */
	private function _checkUnusedPrivateFunctions() {

		if ($this->_isActive('checkUnusedPrivateFunctions')) {

			// We make a diff between the list of declared private functions and the list of called functions.
			// This is a very simple and approximative test, we don't test that the called function is from the good class.
			// The usedFunctions array contains a lot of false positives
			$uncalledFunctions = array_diff($this->_privateFunctions, $this->_usedFunctions);

			foreach ($uncalledFunctions as $uncalledFunction) {

				$msg = sprintf(PHPCHECKSTYLE_UNUSED_PRIVATE_FUNCTION, $uncalledFunction);
				// Direct call to reporter to include a custom line number.
				$this->_reporter->writeError($this->_privateFunctionsStartLines[$uncalledFunction], 'checkUnusedPrivateFunctions', $msg, $this->_config->getTestLevel('checkUnusedPrivateFunctions'));
			}
		}
	}

	/**
	 * Check for unused variables in the file.
	 *
	 * This function is launched at the end of a file.
	 */
	private function _checkUnusedVariables() {

		if ($this->_isActive('checkUnusedVariables')) {

			foreach ($this->_variables as $variableName => $lineNumber) {
				if (($lineNumber != "used") && !($this->_isClass || $this->_isView)) {
					$msg = sprintf(PHPCHECKSTYLE_UNUSED_VARIABLE, $variableName);
					$this->_reporter->writeError($lineNumber, 'checkUnusedVariables', $msg, $this->_config->getTestLevel('checkUnusedVariables'));
				}
			}
		}
	}

	/**
	 * Check for unused code.
	 *
	 * Dead code after a return or a throw TOKEN.
	 *
	 * @param String $endToken The anme of the end token (RETURN or THROW)
	 */
	private function _checkUnusedCode($endToken) {
		if ($this->_isActive('checkUnusedCode')) {

			// The check is done only when we are at the root level of a function
			if ($this->_getCurrentStackItem()->type == 'FUNCTION') {

				// Find the end of the return statement
				$pos = $this->tokenizer->findNextStringPosition(';');

				// Find the next valid token after the return statement
				$nextValidToken = $this->tokenizer->peekNextValidToken($pos);
				$nextValidToken = $this->tokenizer->peekNextValidToken($nextValidToken->position);
					
				// Find the end of the function or bloc of code
				$posClose = $this->tokenizer->findNextStringPosition('}');

				// If the end of bloc if not right after the return statement, we have dead code
				if ($posClose > $nextValidToken->position) {
					$msg = sprintf(PHPCHECKSTYLE_UNUSED_CODE, $this->_getCurrentStackItem()->name, $endToken);
					$this->_writeError('checkUnusedCode', $msg);
				}
			}
		}
	}

	/**
	 * Check for unused function parameters.
	 *
	 * This function is launched at the end of a function
	 */
	private function _checkUnusedFunctionParameters() {

		if ($this->_isActive('checkUnusedFunctionParameters')) {

			foreach ($this->_functionParameters as $variableName => $value) {
				if ($value != "used") {
					$msg = sprintf(PHPCHECKSTYLE_UNUSED_FUNCTION_PARAMETER, $this->_currentFunctionName, $variableName);
					$this->_writeError('checkUnusedFunctionParameters', $msg);
				}
			}
		}
	}

	/**
	 * Check the variable use.
	 *
	 * This function is launched when the current token is T_VARIABLE
	 *
	 * @param String $text The variable name
	 */
	private function _processVariable($text) {

		// Check the variable naming
		if (!in_array($text, $this->_systemVariables)) {
			$this->_checkVariableNaming($text);
		}

		// Check if the variable is not a deprecated system variable
		$this->_checkDeprecation($text);

		// Check if the variable is a function parameter
		if (!empty($this->_functionParameters[$text]) && $this->_inFunction) {

			$this->_functionParameters[$text] = "used";

		} else if (!$this->_inFunctionStatement) {

			// Global variable
			$pos = $this->tokenizer->getCurrentPosition();
			$nextTokenInfo = $this->tokenizer->peekNextValidToken($pos);

			// if the next token is an equal, we suppose that this is an affectation
			$nextTokenText = $this->tokenizer->extractTokenText($nextTokenInfo->token);
			$isAffectation = ($nextTokenText == "="
			|| $nextTokenText == "+="
			|| $nextTokenText == "*="
			|| $nextTokenText == "/="
			|| $nextTokenText == "-="
			|| $nextTokenText == "%="
			|| $nextTokenText == "&="
			|| $nextTokenText == "|="
			|| $nextTokenText == "^="
			|| $nextTokenText == "<<="
			|| $nextTokenText == ">>="
			|| $nextTokenText == ".=");

			// Check if the variable has already been met
			if (empty($this->_variables[$text]) && !in_array($text, $this->_systemVariables)) {
				// The variable is met for the first time
				$this->_variables[$text] = $this->lineNumber; // We store the first declaration of the variable
			} else if ($isAffectation) {
				// The variable is reaffected another value, this doesn't count as a valid use.
			} else {

				// Manage the case of $this->attribute
				if ($text == '$this') {
					if ($this->tokenizer->checkProvidedToken($nextTokenInfo->token, T_OBJECT_OPERATOR)) {

						$nextTokenInfo2 = $this->tokenizer->peekNextValidToken($nextTokenInfo->position);
						// This does not look like a function call, it should be a class attribute.
						// We eliminate the $this-> part
						$text = '$'.$this->tokenizer->extractTokenText($nextTokenInfo2->token);

					}
				}

				// The variable is met again, we suppose we have used it for something
				$this->_variables[$text] = "used";
			}
		}

	}

	/**
	 * Process the return token.
	 *
	 * This function is launched when the current token is T_RETURN
	 */
	private function _processReturn() {
		// Remember that the current function does return something (for PHPDoc)
		$this->_functionReturns = true;

		// Search for unused code after the return
		$this->_checkUnusedCode('RETURN');
	}

	/**
	 * Process the throw token.
	 *
	 * This function is launched when the current token is T_THROW
	 */
	private function _processThrow() {

		// Remember that the current function does throw an exception
		$this->_functionThrows = true;

		// Search for unused code after the throw of an exception
		$this->_checkUnusedCode('THROW');
	}


	/**
	 * Process the start of a heredoc block.
	 *
	 * This function is launched when the current token is T_START_HEREDOC.
	 */
	private function _processStartHeredoc() {

		$this->_getCurrentStackItem()->inHeredoc = true;

		// Rule the "checkHeredoc" rule
		$this->_checkHeredoc();
	}

	/**
	 * Process the end of a heredoc block.
	 *
	 * This function is launched when the current token is T_END_HEREDOC.
	 */
	private function _processEndHeredoc() {

		$this->_getCurrentStackItem()->inHeredoc = false;

	}


	/**
	 * Check for presence of heredoc.
	 */
	private function _checkHeredoc() {
		if ($this->_isActive('checkHeredoc')) {
			$this->_writeError('checkHeredoc', PHPCHECKSTYLE_HEREDOC);
		}
	}

	/**
	 * Check for the presence of a white space before and after the text.
	 *
	 * @param String $text The text of the token to test
	 */
	private function _checkSurroundingWhiteSpace($text) {

		$this->_checkWhiteSpaceBefore($text);
		$this->_checkWhiteSpaceAfter($text);
	}

	/**
	 * Check for the presence of a white space before the text.
	 *
	 * @param String $text The text of the token to test
	 */
	private function _checkWhiteSpaceBefore($text) {
		if ($this->_isActive('checkWhiteSpaceBefore')) {

			$exceptions = $this->_config->getTestExceptions('checkWhiteSpaceBefore');

			if (empty($exceptions) || !in_array($text, $exceptions)) {

				if (!$this->tokenizer->checkProvidedToken($this->prvsToken, T_WHITESPACE)) {
					$msg = sprintf(PHPCHECKSTYLE_SPACE_BEFORE_TOKEN, $text);
					$this->_writeError('checkWhiteSpaceBefore', $msg);
				}

			}
		}
	}

	/**
	 * Check for the abscence of a white space before the text.
	 *
	 * @param String $text The text of the token to test
	 */
	private function _checkNoWhiteSpaceBefore($text) {
		if ($this->_isActive('noSpaceBeforeToken')) {

			$exceptions = $this->_config->getTestExceptions('noSpaceBeforeToken');
			if (empty($exceptions) || !in_array($text, $exceptions)) {
				if ($this->tokenizer->checkProvidedToken($this->prvsToken, T_WHITESPACE)) {

					// To avoid false positives when using a space indentation system, check that we are on the same line as the previous valid token
					$prevValid = $this->tokenizer->peekPrvsValidToken();
					if ($prevValid->lineOffset == 0) {
						$msg = sprintf(PHPCHECKSTYLE_NO_SPACE_BEFORE_TOKEN, $text);
						$this->_writeError('noSpaceBeforeToken', $msg);
					}
				}
			}
		}
	}

	/**
	 * Check for the presence of a white space after the text.
	 *
	 * @param String $text The text of the token to test
	 */
	private function _checkWhiteSpaceAfter($text) {
		if ($this->_isActive('checkWhiteSpaceAfter')) {

			$exceptions = $this->_config->getTestExceptions('checkWhiteSpaceAfter');
			if (empty($exceptions) || !in_array($text, $exceptions)) {

				if (!$this->tokenizer->checkNextToken(T_WHITESPACE)) {
					// In case of new line or a php closing tag it's OK
					if (!($this->tokenizer->checkNextToken(T_NEW_LINE) || $this->tokenizer->checkNextToken(T_CLOSE_TAG))) {
						$msg = sprintf(PHPCHECKSTYLE_SPACE_AFTER_TOKEN, $text);
						$this->_writeError('checkWhiteSpaceAfter', $msg);
					}
				}
			}
		}
	}

	/**
	 * Check for the abscence of a white space after the text.
	 *
	 * @param String $text The text of the token to test
	 */
	private function _checkNoWhiteSpaceAfter($text) {
		if ($this->_isActive('noSpaceAfterToken')) {

			$exceptions = $this->_config->getTestExceptions('noSpaceAfterToken');
			if (empty($exceptions) || !in_array($text, $exceptions)) {

				if ($this->tokenizer->checkNextToken(T_WHITESPACE)) {
					$msg = sprintf(PHPCHECKSTYLE_NO_SPACE_AFTER_TOKEN, $text);
					$this->_writeError('noSpaceAfterToken', $msg);
				}
			}
		}
	}

	/**
	 * Avoid using unary operators (++ or --) inside a control statement.
	 */
	private function _checkUnaryOperator() {
		if ($this->_isActive('checkUnaryOperator')) {

			// If the control statement is not listed as an exception
			$exceptions = $this->_config->getTestExceptions('checkUnaryOperator');

			if (empty($exceptions) || !in_array($this->_currentStatement, $exceptions) || $this->_inArrayStatement) {
				// And if we are currently in a control statement or an array statement
				if ($this->_inControlStatement || $this->_inArrayStatement) {
					$this->_writeError('checkUnaryOperator', PHPCHECKSTYLE_UNARY_OPERATOR);
				}
			}
		}
	}

	/**
	 * Move to the next token.
	 */
	private function _moveToken() {
		$this->prvsToken = $this->token;
		$this->token = $this->tokenizer->getNextToken();
	}

	/**
	 * Check if the current line exceeds the maxLineLength allowed.
	 */
	private function _checkLargeLine() {

		// If the current token is HTML we don't check the line size
		if (!$this->tokenizer->checkProvidedToken($this->token, T_INLINE_HTML)) {

			$maxLength = $this->_config->getTestProperty('lineLength', 'maxLineLength');
			$lineString = ""; // String assembled from tokens
			$currentTokenIndex = $this->tokenizer->getCurrentPosition() - 1;
			$currentToken = $this->tokenizer->peekTokenAt($currentTokenIndex);

			do {
				$currentTokenString = $this->tokenizer->extractTokenText($currentToken);
				$lineString .= $currentTokenString;

				$currentTokenIndex += 1;
				$currentToken = $this->tokenizer->peekTokenAt($currentTokenIndex);

				$isNewLine = $this->tokenizer->checkProvidedToken($currentToken, T_NEW_LINE);
				$isNull = $this->tokenizer->peekTokenAt($currentTokenIndex) == null;
			} while (!($isNull || $isNewLine));

			$lineLength = strlen($lineString);

			// Reporting the error if the line length exceeds the defined maximum.
			if ($lineLength > $maxLength) {
				// Does not report if the line is a multiline comment - i.e. has /* in it)
				if (strpos($lineString, "/*")) {
					return;
				}
				$msg = sprintf(PHPCHECKSTYLE_LONG_LINE, $lineLength, $maxLength);
				$this->_writeError('lineLength', $msg);
			}
		}
	}

	/**
	 * Checks for presence of tab in the whitespace character string.
	 *
	 * @param String $whitespaceString the whitespace string used for indentation
	 */
	private function _checkIndentation($whitespaceString) {
		if ($this->_isActive('indentation')) {

			$indentationType = $this->_config->getTestProperty('indentation', 'type');

			// If indentation type is space, we look for tabs in the string
			if (strtolower($indentationType) == 'space' || strtolower($indentationType) == 'spaces') {
				$tabfound = preg_match("/\t/", $whitespaceString);
				if ($tabfound) {
					$this->_writeError('indentation', PHPCHECKSTYLE_INDENTATION_TAB);
				}

				$indentationNumber = $this->_config->getTestProperty('indentation', 'number');
				if (empty($indentationNumber)) {
					$indentationNumber = 2;
				}
				$this->_checkIndentationLevel($whitespaceString, $indentationNumber);
			} else if (strtolower($indentationType) == 'tab' || strtolower($indentationType) == 'tabs') {
				// If indentation type is tabs, we look for whitespace in the string
				$whitespacefound = preg_match("/[ ]/", $whitespaceString);
				if ($whitespacefound) {
					$this->_writeError('indentation', PHPCHECKSTYLE_INDENTATION_WHITESPACE);
				}
			}

		}

	}

	/**
	 * Check the indentation level.
	 *
	 * @param String $whitespaceString the whitespace string used for indentation
	 * @param String $indentationNumber the expected number of whitespaces used for indentation
	 */
	private function _checkIndentationLevel($whitespaceString, $indentationNumber) {

		//doesn't work if we are not in a class
		if (!$this->_inClass) {
			return;
		}

		// don't check empty lines and when we are in a control statement
		if ($this->_inControlStatement || $this->_inFuncCall || !isset($this->lineNumber) || $this->tokenizer->checkNextToken(T_NEW_LINE) || $this->tokenizer->checkNextValidTextToken(")")) {
			return;
		}

		$previousToken = $this->tokenizer->peekPrvsToken();
		// only check a line once
		if (!isset($this->indentationLevel['previousLine']) || $this->lineNumber != $this->indentationLevel['previousLine']) {
			$nesting = count($this->_branchingStack);
			if ($this->tokenizer->checkNextValidTextToken("{")) {
				$nesting++;
			}

			$expectedIndentation = $nesting * $indentationNumber;
			$indentation = strlen($whitespaceString);
			if ($previousToken[0] != T_NEW_LINE) {
				$indentation = 0;
			}

			// don't check when the line is a comment
			if ($this->tokenizer->checkNextToken(T_COMMENT)) {
				return;
			}

			// Control switch statement indentation
			if ($this->_inSwitch) {
				if (!$this->tokenizer->checkNextToken(T_CASE) && !$this->tokenizer->checkNextToken(T_DEFAULT)) {
					$expectedIndentation = $expectedIndentation + $indentationNumber;
				}

				// Don't check brackets in a switch
				if ($this->tokenizer->checkNextValidTextToken("{") || $this->tokenizer->checkNextValidTextToken("}")) {
					return;
				}
			}

			// the indentation is almost free if it is a multiligne array
			if ($this->tokenizer->checkNextToken(T_CONSTANT_ENCAPSED_STRING) || $this->tokenizer->checkNextToken(T_OBJECT_OPERATOR) || $this->tokenizer->checkNextToken(T_ARRAY) || $this->tokenizer->checkNextToken(T_NEW)) {
				if (($expectedIndentation + 2) > $indentation) {
					$msg = sprintf(PHPCHECKSTYLE_INDENTATION_LEVEL_MORE, $expectedIndentation, $indentation);
					$this->_writeError('indentationLevel', $msg);
				}
			} elseif ($expectedIndentation != $indentation) {
				$msg = sprintf(PHPCHECKSTYLE_INDENTATION_LEVEL, $expectedIndentation, $indentation);
				$this->_writeError('indentationLevel', $msg);
			}
		}
		$this->indentationLevel['previousLine'] = $this->lineNumber;
	}

	/**
	 * Checks if the block of code need braces.
	 *
	 * This function is called we the current token is ) and we are in a control statement.
	 */
	private function _checkNeedBraces() {
		if ($this->_isActive('needBraces')) {

			$stmt = strtolower($this->_currentStatement);
			if ($stmt == "if" || $stmt == "else" || $stmt == "elseif" || $stmt == "do" || ($stmt == "while" && !$this->_getCurrentStackItem()->afterDoStatement) || $stmt == "for" || $stmt == "foreach") {
				if (!$this->tokenizer->checkNextValidTextToken("{")) {
					$msg = sprintf(PHPCHECKSTYLE_NEED_BRACES, $stmt);
					$this->_writeError('needBraces', $msg);
				}
			}
			if ($stmt == "while") {
				$this->_getCurrentStackItem()->afterDoStatement = false;
			}

		}
	}

	/**
	 * Process a comment.
	 *
	 * @param Integer $tok the token
	 * @param String $text the text of the comment
	 */
	private function _processComment($tok, $text) {

		// Count the lines of comment
		if ($tok == T_COMMENT) {
			$this->_ncssTotalSingleComment++;
			$this->_ncssFileSingleComment++;
		} else if ($tok == T_ML_COMMENT) {
			$this->_ncssTotalMultiComment++;
			$this->_ncssFileMultiComment++;
		} else if ($tok == T_DOC_COMMENT) {
			$this->_ncssTotalPhpdoc++;
			$this->_ncssFilePhpdoc++;
			if ($this->_isFileDocComment()) {
				$this->_processAnnotation(T_FILE, $text);
			}
		}

		// Manage new lines inside commments
		$subTokens = preg_split('#(\r\n|\n|\r)#', $text, -1);

		foreach ($subTokens as $subToken) {

			if ($tok == T_DOC_COMMENT) {
				$this->_ncssTotalLinesPhpdoc++;
				$this->_ncssFileLinesPhpdoc++;
			}

			// Count the @params, @returns and @throw
			if (stripos($subToken, '@param') !== false) {
				$this->_docblocNbParams++;
			}
			if (stripos($subToken, '@return') !== false) {
				$this->_docblocNbReturns++;
			}
			if (stripos($subToken, '@throw') !== false) {
				$this->_docblocNbThrows++;
			}

			// Increment the lines number (one comment is only one token)
			$this->lineNumber++;
		}
		$this->lineNumber--;  // One end of line is already counted

		// Check if the comment starts with '#'
		if ($this->_isActive('noShellComments')) {
			$s = strpos($text, '#');
			if ($s === 0) {
				$this->_writeError('noShellComments', PHPCHECKSTYLE_NO_SHELL_COMMENTS);
			}
		}

		// Check if the comment contains a TODO
		if ($this->_isActive('showTODOs')) {
			$s = stripos($text, 'TODO');
			if ($s != FALSE) {
				$msg = sprintf(PHPCHECKSTYLE_TODO, substr($text, stripos($text, 'TODO') + 4));
				$this->_writeError('showTODOs', $msg);
			}
		}

	}

	/**
	 * Utility function to determine whether or not a T_DOC_COMMENT
	 * is at the file level or belongs to a class, interface, method
	 * or member.<br>
	 * <b>Note:<b> This will miss file doc blocks with no new line
	 * before the next token - in this case we can never be sure
	 * what the doc block is aimed at.
	 *
	 * @return true if the next token is a T_NEW_LINE or T_DOC_COMMENT,
	 * 			false otherwise.
	 */
	private function _isFileDocComment() {
		$tokenPosition = $this->tokenizer->getCurrentPosition();
		$nextTokenInfo = $this->tokenizer->peekNextValidToken(++$tokenPosition, true);
		$nextToken = $nextTokenInfo->token;
		return !$this->_inClassStatement && !$this->_inInterfaceStatement && ($nextToken[0] == T_NEW_LINE || $nextToken[0] == T_DOC_COMMENT);
	}

	/**
	 * Check for the existence of a docblock for the current token
	 *  o  go back and find the previous token that is not a whitespace
	 *  o  if it is a access specifier (private, public etc), then
	 *     see if private members are excluded from comment check
	 *     (input argument specified this). if we find an access
	 *     specifier move on to find the next best token
	 *  o  if it is ABSTRACT or STATIC specifier move on to find the next best token
	 *  o  if the found token is a T_DOC_COMMENT, then we have a docblock
	 *
	 * This, of course, assumes that the function/class/interface has to be
	 * immediately preceded by docblock. Even regular comments are not
	 * allowed, which I think is okay.
	 *
	 * @param Integer $token T_CLASS, T_FUNCTION or T_INTERFACE
	 * @return true is docblock is found
	 */
	private function _checkDocExists($token) {

		// current token = the token after T_CLASS, T_FUNCTION or T_INTERFACE
		//
		// token positions:
		//  .  curToken - 1 = T_CLASS/T_FUNCTION/T_INTERFACE
		//  .  curToken - 2 = whitespace before T_CLASS/T_FUNCTION/T_INTERFACE
		//  .  curToken - 3 = T_ABSTRACT/T_PUBLIC/T_PROTECTED/T_PRIVATE/T_STATIC
		//                    or T_DOC_COMMENT, if it is present
		//

		$isPrivateExcluded = $this->_config->getTestProperty('docBlocks', 'excludePrivateMembers');

		// Locate the function, class or interface token
		$functionTokenPosition = $this->tokenizer->getCurrentPosition();
		while (true) {
			//the type - function, class or interface. (Horribly named).
			$functionToken = $this->tokenizer->peekTokenAt($functionTokenPosition);

			$isFunction = $this->tokenizer->checkProvidedToken($functionToken, T_FUNCTION);
			$isClass = $this->tokenizer->checkProvidedToken($functionToken, T_CLASS);
			$isInterface = $this->tokenizer->checkProvidedToken($functionToken, T_INTERFACE);

			if ($isFunction || $isClass || $isInterface) {
				break;
			}
			$functionTokenPosition--;
		}

		// Records the type, as well as the type name for more precise error reporting.
		// Two positions forward from declaration of type.
		$typeToken = $this->tokenizer->peekTokenAt($functionTokenPosition);
		$type = $this->tokenizer->extractTokenText($typeToken);
		$nameToken = $this->tokenizer->peekTokenAt($functionTokenPosition + 2);
		$name = $this->tokenizer->extractTokenText($nameToken);

		$isOldStyleConstructor = (strtolower($name) == strtolower($this->_currentClassname));
		$isNewStyleConstructor = (strtolower($name) == '__construct');
		if ($isOldStyleConstructor || $isNewStyleConstructor) {
			$type = "constructor";
		}

		$found = false;
		$isPrivate = false;
		$docTokenPosition = $functionTokenPosition - 1;

		// Go backward and look for a T_DOC_COMMENT
		while (true) {
			$docToken = $this->tokenizer->peekTokenAt($docTokenPosition);

			if (is_array($docToken)) {
				$tokenToIgnoreList = array(T_STATIC,
				T_ABSTRACT,
				T_PROTECTED,
				T_PUBLIC,
				T_WHITESPACE,
				T_TAB,
				T_COMMENT,
				T_ML_COMMENT,
				T_NEW_LINE);
				// if the token is in the list above.
				if ($this->_tokenIsInList($docToken, $tokenToIgnoreList)) {
					// All these tokens are ignored
				} else if ($this->tokenizer->checkProvidedToken($docToken, T_PRIVATE)) {
					$isPrivate = true; // we are in a private function
				} else if ($this->tokenizer->checkProvidedToken($docToken, T_DOC_COMMENT)) {
					// We have found a doc comment
					$found = true;
					break;
				} else {
					// Any other token found, we stop
					$found = false;
					break;
				}
			} else {
				// The previous token is a string
				break;
			}
			$docTokenPosition--;

			if ($docTokenPosition == 0) {
				break;
			}
		}
		if ($found) {
			// Doc found, look for annotations
			$this->_processAnnotation($token, $docToken[1]);

		} else {
			// No documentation found
			if ($this->_isActive('docBlocks') && !($isPrivateExcluded && $isPrivate)) {
				$msg = sprintf(PHPCHECKSTYLE_MISSING_DOCBLOCK, $type, $name);
				$this->_writeError('docBlocks', $msg);
			}
		}
	}

	/**
	 * Process PHP_DOC looking for annotations.
	 *
	 * @param Integer $token T_CLASS or T_FUNCTION
	 * @param String $comment the comment to analyse
	 */
	private function _processAnnotation($token, $comment) {

		// Read the documentation line by line
		$subToken = strtok($comment, PHP_EOL);
		while ($subToken !== false) {

			// Manage annotations
			$pos = stripos($subToken, "@SuppressWarnings");
			if ($pos !== false) {
				$suppressedCheck = trim(substr($subToken, $pos + strlen("@SuppressWarnings")));
				$supprArray = explode(' ', $suppressedCheck);
				$suppressedCheck = trim($supprArray[0]);
				// Store the suppressed warning in the corresponding array
				if ($token == T_CLASS) {
					$this->_classSuppressWarnings[] = $suppressedCheck;
				} elseif ($token == T_INTERFACE) {
					$this->_interfaceSuppressWarnings[] = $suppressedCheck;
				} elseif ($token == T_FUNCTION) {
					$this->_functionSuppressWarnings[] = $suppressedCheck;
				} elseif ($token == T_FILE) {
					$this->_fileSuppressWarnings[] = $suppressedCheck;
				}
			}

			$subToken = strtok(PHP_EOL);
		}
	}

	/**
	 * Display the current branching stack.
	 */
	private function _dumpStack() {
		foreach ($this->_branchingStack as $item) {
			echo $item->type;
			if ($item->type == "FUNCTION" || $item->type == "INTERFACE" || $item->type == "CLASS") {
				echo "(".$item->name.")";
			}
			echo " -> ";
		}
		echo PHP_EOL;
	}

	/**
	 * Return the top stack item.
	 *
	 * @return StatementItem
	 */
	private function _getCurrentStackItem() {
		$topItem = end($this->_branchingStack);
		if ($topItem != null) {
			return $topItem;
		} else {
			// In case of a empty stack, we are at the root of a PHP file (with no class or function).
			// We return the default values
			return new StatementItem(); 
		}
	}
	/**
	 * Check for silenced call to functons.
	 *
	 * @param String $text The text of the token to test
	 */
	private function _checkSilenced($text) {
		if ($this->_isActive('checkSilencedError')) {

			$exceptions = $this->_config->getTestExceptions('checkSilencedError');
			if (empty($exceptions) || !in_array($text, $exceptions)) {
				if ($this->prvsToken == "@") {
					$this->_writeError('checkSilencedError', PHPCHECKSTYLE_SILENCED_ERROR);
				}
			}
		}
	}

	/**
	 * Check for deprecated functions.
	 *
	 * @param String $text The text of the token to test
	 */
	private function _checkDeprecation($text) {
		if ($this->_isActive('checkDeprecation')) {

			$key = strtolower($text);
			if (array_key_exists($key, $this->_deprecatedFunctions)) {
				$msg = sprintf(PHPCHECKSTYLE_DEPRECATED_FUNCTION, $this->_deprecatedFunctions[$key]['old'], $this->_deprecatedFunctions[$key]['version'], $this->_deprecatedFunctions[$key]['new']);
				$this->_writeError('checkDeprecation', $msg);
			}
		}
	}

	/**
	 * Check for goto.
	 */
	private function _checkGoTo() {
		if ($this->_isActive('checkGoTo')) {
			/* means we've encountered a goto and the rule is active, so complain */
			$this->_writeError('checkGoTo', PHPCHECKSTYLE_GOTO);
		}
	}

	/**
	 * Check for continue.
	 */
	private function _checkContinue() {
		if ($this->_isActive('checkContinue')) {
			/* means we've encountered a continue and the rule is active, so complain */
			$this->_writeError('checkContinue', PHPCHECKSTYLE_CONTINUE);
		}
	}

	/**
	 * Tell is a check is active.
	 *
	 * @param String $check the name of the check
	 * @return a boolean
	 */
	private function _isActive($check) {

		// Check if the check is configured
		$active = $this->_config->getTest($check);

		$active = $active && !(in_array($check, $this->_functionSuppressWarnings) || in_array($check, $this->_classSuppressWarnings) || in_array($check, $this->_interfaceSuppressWarnings) || in_array($check, $this->_fileSuppressWarnings));

		return $active;
	}

	/**
	 * Output the error to the selected reporter.
	 *
	 * @param String $check the name of the check
	 * @param String $message the error message
	 * @param int $lineNumber optional line number
	 */
	private function _writeError($check, $message, $lineNumber = null) {
		if ($lineNumber === null) {
			$lineNumber = $this->lineNumber;
		}

		$level = $this->_config->getTestLevel($check);
		if ($level == null) {
			$level = "WARNING";
		}

		$this->_reporter->writeError($lineNumber, $check, $message, $level);
	}

	/**
	 * Checks if a token is in the type of token list.
	 *
	 * @param $tokenToCheck 	the token to check.
	 * @param $tokenList 		an array of token types, e.g. T_NEW_LINE, T_DOC_COMMENT, etc.
	 *
	 * @return true if the token is found, false if it is not.
	 */
	private function _tokenIsInList($tokenToCheck, $tokenList) {
		foreach ($tokenList as $tokenInList) {
			if ($this->tokenizer->checkProvidedToken($tokenToCheck, $tokenInList)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Handles the tracking of string starts and ends to avoid checking of braces inside strings.
	 *
	 * @param String $stringTocheck
	 * 								The string to check for quotes
	 */
	private function _trackStringOpenOrClose($stringTocheck) {
		if (!$this->_inString) {
			$this->_inString = true;
			$this->_stringStartCharacter = $stringTocheck;
		}
		else if ($this->_stringStartCharacter == $stringTocheck) {
			$this->_inString = false;
			$this->_stringStartCharacter = null;
		}
	}


}
