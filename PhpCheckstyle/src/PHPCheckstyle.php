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
require_once PHPCHECKSTYLE_HOME_DIR."/src/reporter/PlainFormatReporter.php";
require_once PHPCHECKSTYLE_HOME_DIR."/src/reporter/XmlFormatReporter.php";
require_once PHPCHECKSTYLE_HOME_DIR."/src/reporter/ConsoleReporter.php";
require_once PHPCHECKSTYLE_HOME_DIR."/src/reporter/XmlNCSSReporter.php";

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
	private $_csLeftBracket = 0;
	private $_fcLeftBracket = 0;
	private $inDoWhile = false;

	private $token = false;
	private $prvsToken = false;
	private $lineNumber = 0; // Store the current line number

	private $_inControlStatement = false; // We are in a control statement declaration (for, if, while, ...)
	private $_inArrayStatement = false; // We are in a array statement
	private $_inFunctionStatement = false; // We are in a function statement (declaration)
	private $_inFuncCall = false; // We are in a function call
	private $_inFunction = false; // We are inside a function
	private $_privateFunctions = array(); // The list of private functions in the class
	private $_privateFunctionsStartLines = array();
	private $_functionParameters = array(); // The list of function parameters
	private $_usedFunctions = array(); // The list of functions that are used in the class
	private $_variables = array(); // The variables used
	private $_inSwitch = false; // We are inside a switch statement
	private $_switchLevel = 0;
	private $_switchHasDefault = false; // The switch has a default statement
	private $_caseHasBreak = false; // The case has a break
	private $_caseStartLine = 0;
	private $_nbFunctionParameters = 0; // Count the number of parameters of the current function
	private $_justAfterFuncStmt = false; // We are just after a control statement (last } )
	private $_justAfterControlStmt = false; // We are just after a function statement (last } )
	private $_functionStartLine = 0; // Starting line of the current function
	private $_switchStartLine = 0; // Starting line of the current switch statement
	private $_functionReturns = false; // Does the function return a value ?
	private $_functionThrows = false; // Does the function throw an exception ?
	private $_functionLevel = 0; // Level of Nesting of the function
	private $_isFunctionPrivate = false; // Is the function a private function
	private $_inClassStatement = false;
	private $_constantDef = false;
	private $_currentClassname = null;
	private $_currentFilename = null;
	private $_currentStatement = false;

	private $_docblocNbParams = 0; // Number of @params in the docblock of a function
	private $_docblocNbReturns = 0; // Number of @return in the docblock of a function
	private $_docblocNbThrows = 0; // Number of @throw in the docblock of a function

	private $_levelOfNesting = 0;
	private $_branchingStack = array();
	private $_cyclomaticComplexity = 0;

	// For MVC frameworks
	private $_isView = false;
	private $_isModel = false;
	private $_isController = false;
	private $_isClass = false;

	/**
	 * These functions are not tested for naming.
	 *
	 * @var array   List of the magic methods
	 * @link http://www.php.net/manual/en/language.oop5.magic.php
	 */
	private $_specialFunctions = array("__construct", "__destruct", "__call", "__get", "__set", "__isset", "__unset", "__sleep", "__wakeup", "__toString", "__set_state", "__clone", "__autoload");

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

	private $_systemVariables = array('$this', '$_GET', '$_POST', '$_FILES', '$_COOKIE', '$_SESSION', '$_ENV', '$_SERVER');

	// The class used to export the result
	private $_reporter;

	// The class used to export the count of lines
	private $_lineCountReporter;

	private $_excludeList = array();
	private $_rootSourceDir = "";

	private $_config;

	// Informations used to count lines of code
	private $_ncssTotalClasses = 0;
	private $_ncssTotalFunctions = 0;
	private $_ncssTotalLinesOfCode = 0;
	private $_ncssTotalPhpdoc = 0;
	private $_ncssTotalLinesPhpdoc = 0;
	private $_ncssTotalSingleComment = 0;
	private $_ncssTotalMultiComment = 0;
	private $_ncssFileClasses = 0;
	private $_ncssFileFunctions = 0;
	private $_ncssFileLinesOfCode = 0;
	private $_ncssFilePhpdoc = 0;
	private $_ncssFileLinesPhpdoc = 0;
	private $_ncssFileSingleComment = 0;
	private $_ncssFileMultiComment = 0;

	/**
	 * Constructor.
	 *
	 * @param $outformat output format "text" or "html".
	 * 					Accordingly creates a formatter object
	 * @param $outfile  output file where results are stored.
	 * 					Note that in case of "html" format, the output is xml and run.php transforms the xml file into html
	 * @access public
	 */
	public function PHPCheckstyle($outformat, $outfile, $linecountfile = null) {

		// Initialise the Tokenizer
		$this->tokenizer = new TokenUtils();

		// Initialise the Reporter
		if ($outformat == "text") {
			$this->_reporter = new PlainFormatReporter($outfile);
		} elseif ($outformat == "html") {
			$this->_reporter = new XmlFormatReporter($outfile);
		} elseif ($outformat == "console") {
			$this->_reporter = new ConsoleReporter();
		}

		if ($linecountfile != null) {
			$this->_lineCountReporter = new XmlNCSSReporter($linecountfile);
		}

		// Initialise the configuration
		$this->_config = new CheckStyleConfig("");
		$this->_config->parse();

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
		$this->_rootSourceDir = $src;
		$this->_excludeList = $excludes;
		$files = $this->_getAllPhpFiles($src, $excludes);

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
			$this->_lineCountReporter->writeTotalCount(count($files), $this->_ncssTotalClasses, $this->_ncssTotalFunctions, $this->_ncssTotalLinesOfCode, $this->_ncssTotalPhpdoc, $this->_ncssTotalLinesPhpdoc, $this->_ncssTotalSingleComment, $this->_ncssTotalMultiComment);
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
		$this->_csLeftBracket = 0;
		$this->_fcLeftBracket = 0;
		$this->inDoWhile = false;

		$this->_inControlStatement = false;
		$this->_inArrayStatement = false;
		$this->_inFunctionStatement = false;
		$this->_inFunction = false;
		$this->_privateFunctions = array();
		$this->_usedFunctions = array();
		$this->_variables = array();
		$this->_privateFunctionsStartLines = array();
		$this->_inSwitch = false;
		$this->_switchLevel = 0;
		$this->_caseHasBreak = false;
		$this->_caseStartLine = 0;
		$this->_inFuncCall = false;
		$this->_nbFunctionParameters = 0;
		$this->_justAfterFuncStmt = false;
		$this->_justAfterControlStmt = false;
		$this->_functionStartLine = 0;
		$this->_functionReturns = false;
		$this->_functionThrows = false;
		$this->_isFunctionPrivate = false;
		$this->_currentStatement = false;
		$this->_inClassStatement = false;

		$this->_ncssFileClasses = 0;
		$this->_ncssFileFunctions = 0;
		$this->_ncssFileLinesOfCode = 0;
		$this->_ncssFilePhpdoc = 0;
		$this->_ncssFileLinesPhpdoc = 0;
		$this->_ncssFileSingleComment = 0;
		$this->_ncssFileMultiComment = 0;

		$this->_currentClassname = null;
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
		if (stripos($f, 'class') !== false) { // simple simple data objects
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
		if ($this->_config->getTest('noFileCloseTag')) {
			if ($this->tokenizer->checkProvidedToken($this->prvsToken, T_CLOSE_TAG)) {
				// Closing tag is not recommended since PHP 5.0
				$this->_reporter->writeError($this->lineNumber, PHPCHECKSTYLE_END_FILE_CLOSE_TAG, $this->_config->getTestLevel('noFileCloseTag'));
			}
		}

		// Inner HTML is OK for views but not for other classes (controllers, models, ...)
		if ($this->_config->getTest('noFileFinishHTML') && !$this->_isView) {
			if ($this->tokenizer->checkProvidedToken($this->prvsToken, T_INLINE_HTML)) {
				$this->_reporter->writeError($this->lineNumber, PHPCHECKSTYLE_END_FILE_INLINE_HTML, $this->_config->getTestLevel('noFileFinishHTML'));
			}
		}

		// Check for unused private functions
		$this->_checkUnusedPrivateFunctions();

		// Check for unused variables
		$this->_checkUnusedVariables();

		// Write the count of lines for this file
		if ($this->_lineCountReporter != null) {
			$this->_lineCountReporter->writeFileCount($f, $this->_ncssFileClasses, $this->_ncssFileFunctions, $this->_ncssFileLinesOfCode, $this->_ncssFilePhpdoc, $this->_ncssFileLinesPhpdoc, $this->_ncssFileSingleComment, $this->_ncssFileMultiComment);
		}

	}

	/**
	 * Go through a directory recursively and get all the
	 * php (with extension .php and .tpl) files
	 * Ignores files or subdirectories that are in the _excludeList
	 *
	 * @param $src source directory
	 * @return an array of files that end with .php or .tpl
	 * @access private
	 */
	private function _getAllPhpFiles($src, $excludes, $dir = '') {
		$files[] = array();
		if (!is_dir($src)) {
			$files[] = $src;
		} else {
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
					if (!in_array($relPath, $excludes)) {
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
			echo $this->_levelOfNesting." Line  : ".$this->lineNumber." String : ".$text.PHP_EOL;
			$this->_dumpStack();
		}

		switch ($text) {

		case "{":

			// "{" signifies beginning of a block. We need to look for
			// its position when it is a beginning of a control structure
			// or a function or class definition.

			// Check we have a white space before a curly opening
			$this->_checkWhiteSpaceBefore($text);
			$stackitem = "";

			// if _justAfterFuncStmt is set, the "{" is the beginning of a function definition block
			if ($this->_justAfterFuncStmt) {
				$this->_processFunctionStart();
				$stackitem = "function";
			}

			// if _justAfterControlStmt is set, the "{" is the beginning of a control structure block
			if ($this->_justAfterControlStmt) {
				$this->_processControlStructureStart();
				$stackitem = $this->_currentStatement;
			}

			// if _inClassStatement is set then we are just after a class declaration
			if ($this->_inClassStatement) {
				$this->_inClassStatement = false;
				$stackitem = "class";
			}

			// Check if the block is not empty
			$this->_checkEmptyBlock();

			$this->_levelOfNesting++;
			array_push($this->_branchingStack, $stackitem);

			break;

		case "}":
			// "}" signifies the end of a block
			// currently tests whether this token resides on a new line.
			// This test is desactivated when in a view
			if ($this->_config->getTest('controlCloseCurly') && !($this->_isView)) {
				$previousTokenInfo = $this->tokenizer->peekPrvsValidToken();
				if ($previousTokenInfo->lineOffset == 0) { // the last token was on the same line
					$this->_reporter->writeError($this->lineNumber, PHPCHECKSTYLE_END_BLOCK_NEW_LINE, $this->_config->getTestLevel('controlCloseCurly'));
				}
			}

			$this->_levelOfNesting--;
			array_pop($this->_branchingStack);

			// Test for the end of a switch bloc
			if ($this->_inSwitch && $this->_levelOfNesting == $this->_switchLevel) {
				$this->_processSwitchStop();
			}

			// Test for the end of a function
			if ($this->_levelOfNesting == $this->_functionLevel && $this->_inFunction) {
				$this->_processFunctionStop();
			}

			break;

		case ";":
			// ";" -> end of statement
			// we only need to make sure that we are not hitting ":"
			// before "{" in the case of a control structure, in which
			// case we have a control structure is not using the curly
			// brackets
			if ($this->_justAfterControlStmt) {
				$this->_justAfterControlStmt = false;

				if ($this->_config->getTest('controlStructNeedCurly')) {
					$this->_reporter->writeError($this->lineNumber, PHPCHECKSTYLE_CS_NO_OPEN_CURLY, $this->_config->getTestLevel('controlStructNeedCurly'));
				}
			}

			// ";" should never be preceded by a whitespace
			$this->_checkNoWhiteSpaceBefore($text);
			$this->_checkEmptyStatement();

			break;

		case "-":
			if (!$this->_inFuncCall) {
				$this->_checkWhiteSpaceBefore($text);
			}
			// We allow some '-' signs to skip the the space afterwards for negative numbers
			if (!($this->tokenizer->checkNextToken(T_LNUMBER) || // float number
			$this->tokenizer->checkNextToken(T_DNUMBER))) { // integer
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
			if ($this->_inFuncCall) { // inside a function call
				$this->_fcLeftBracket += 1;
			} elseif ($this->_inControlStatement || $this->_inFunctionStatement) { // inside a function or control statement
				$this->_csLeftBracket += 1;
			}

			$this->_checkNoWhiteSpaceAfter($text);
			break;

		case ")":
			// again the only issue here the space after/before it
			if ($this->_inFuncCall) {
				$this->_fcLeftBracket -= 1;
			} elseif ($this->_inControlStatement || $this->_inFunctionStatement) {
				$this->_csLeftBracket -= 1;
			}
			if ($this->_fcLeftBracket == 0) {
				$this->_inFuncCall = false;
			}
			if ($this->_csLeftBracket == 0) {
				if ($this->_inControlStatement) {
					$this->_inControlStatement = false;
					$this->_checkNeedBraces();
					$this->_justAfterControlStmt = true;
				} elseif ($this->_inFunctionStatement) {
					$this->_inFunctionStatement = false;
					$this->_justAfterFuncStmt = true;
				}
			}

			$this->_checkNoWhiteSpaceBefore($text);
			break;

		case "&":
			// One of the function parameter is passed by reference
			if ($this->_config->getTest('avoidPassingReferences')) {
				if ($this->_inFunctionStatement) {
					$this->_reporter->writeError($this->lineNumber, PHPCHECKSTYLE_PASSING_REFERENCE, $this->_config->getTestLevel('avoidPassingReferences'));
				}
			}
			break;
		case "[":
			$this->_inArrayStatement = true;
			break;
		case "]":
			$this->_inArrayStatement = false;
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
			echo $this->_levelOfNesting;
			echo " Line  : ".$this->lineNumber;
			echo " Token ".$this->tokenizer->getTokenName($tok);
			echo " : ".$text.PHP_EOL;
			$this->_dumpStack();
		}

		// Check if the token is in the list of prohibited tokens
		if ($this->_config->getTest('checkProhibitedTokens') == 1) {
			foreach ($this->_prohibitedTokens as $prohibitedTokens) {
				if ($this->tokenizer->getTokenName($tok) == $prohibitedTokens) {
					$msg = sprintf(PHPCHECKSTYLE_PROHIBITED_TOKEN, $this->tokenizer->getTokenName($tok));
					$this->_reporter->writeError($this->lineNumber, $msg, $this->_config->getTestLevel('checkProhibitedTokens'));
				}
			}
		}

		switch ($tok) {
		case T_COMMENT:
		case T_ML_COMMENT:
		case T_DOC_COMMENT:
			$this->_processComment($tok, $text);
			break;

			// check if shorthand code tags are allowed
			case T_OPEN_TAG:
			if ($this->_config->getTest('noShortPhpCodeTag')) {
				$s = strpos($text, '<?php');
				if ($s === false) {
					$this->_reporter->writeError($this->lineNumber, PHPCHECKSTYLE_WRONG_OPEN_TAG, $this->_config->getTestLevel('noShortPhpCodeTag'));
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
			{
				$this->_checkIndentation($text);
				break;
			}

		case T_INLINE_HTML:
			break;

			// beginning of a function definition
			// check also for existance of docblock
			case T_FUNCTION:
			$this->_checkDocExists();
			$this->_processFunctionStatement();
			break;

			// beginning of a class
			// check also for the existence of a docblock
			case T_CLASS:
			$this->_checkDocExists();
			$this->_processClassStatement();
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
			if ($this->_config->getTest('useBooleanOperators')) {
				$this->_reporter->writeError($this->lineNumber, PHPCHECKSTYLE_USE_BOOLEAN_OPERATORS, $this->_config->getTestLevel('useBooleanOperators'));
			}
			$this->_checkSurroundingWhiteSpace($text);
			break;

			// ASSUMPTION:
			//   that T_STRING followed by "(" is a function call
			//   Actually, I am not sure how good an assumption this is.
			case T_STRING:
			// Check whether this is a function call
			$this->_processFunctionCall($text);
			break;

			// found constant definition
			case T_CONSTANT_ENCAPSED_STRING:
			$this->_checkConstantNaming($text);

			// Manage new lines inside string
			$subToken = strtok($text, PHP_EOL);
			while ($subToken !== false) {
				// Increment the lines number (one comment is only one token)
				$this->lineNumber++;
				$subToken = strtok(PHP_EOL);
			}
			$this->lineNumber--; // One end of line is already counted

			break;

			// Constant part of string with variables
			case T_ENCAPSED_AND_WHITESPACE:
			if ($this->_config->getTest('encapsedVariablesInsideString')) {
				$this->_reporter->writeError($this->lineNumber, PHPCHECKSTYLE_VARIABLE_INSIDE_STRING, $this->_config->getTestLevel('encapsedVariablesInsideString'));
			}
			break;
		case T_CURLY_OPEN: // for protected variables within strings "{$var}"
			$this->_levelOfNesting++;
			array_push($this->_branchingStack, 'curly_open');
			break;
		case T_DOLLAR_OPEN_CURLY_BRACES: // for extended format "${var}"
			$this->_levelOfNesting++;
			array_push($this->_branchingStack, 'dollar_curly_open');
			break;
		case T_NEW_LINE:
			$this->_countLinesOfCode();
			$this->lineNumber++;
			break;
		case T_RETURN:
			$this->_processReturn();
			break;
		case T_THROW:
			$this->_functionThrows = true;
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
			$this->_checkHeredoc();
			break;
		case T_VARIABLE:
			$this->_processVariable($text);
			break;
		default:
			break;
		}
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
	 * @param $text the string containing the constant. note that the
	 *        string also has the quotes (single or double), so we need
	 *        remove them from the string before testing
	 */
	private function _checkConstantNaming($text) {
		if ($this->_constantDef && $this->_config->getTest('constantNaming')) {
			$text = ltrim($text, "\"'");
			$text = rtrim($text, "\"'");
			$ret = preg_match($this->_config->getTestRegExp('constantNaming'), $text);
			if (!$ret) {
				$msg = sprintf(PHPCHECKSTYLE_CONSTANT_NAMING, $text);
				$this->_reporter->writeError($this->lineNumber, $msg, $this->_config->getTestLevel('constantNaming'));
			}

			$this->_constantDef = false;
		}
	}

	/**
	 * Checks to see if the variable follows the naming convention
	 * Variables should only have letters, start with a lowercase and have no underscore.
	 *
	 * @param $text the string containing the variable. note that the
	 *        string also has the quotes (single or double), so we need
	 *        remove them from the string before testing
	 */
	private function _checkVariableNaming($text) {

		if ($this->_config->getTest('variableNaming')) {
			$texttoTest = ltrim($text, "\"'"); // remove the quotes
			$texttoTest = rtrim($texttoTest, "\"'");
			if (strpos($texttoTest, "$") === 0) { // remove the "&"
				$texttoTest = substr($texttoTest, 1);
			}

			$ret = preg_match($this->_config->getTestRegExp('variableNaming'), $texttoTest);
			if (!$ret) {
				$msg = sprintf(PHPCHECKSTYLE_VARIABLE_NAMING, $text);
				$this->_reporter->writeError($this->lineNumber, $msg, $this->_config->getTestLevel('variableNaming'));
			}
		}
	}

	/**
	 * Check the naming of a function.
	 *
	 * @param $text the name of the function.
	 */
	private function _checkFunctionNaming($text) {
		if ($this->_config->getTest('functionNaming')) {

			$ret = preg_match($this->_config->getTestRegExp('functionNaming'), $text);
			if (!$ret) {
				$msg = sprintf(PHPCHECKSTYLE_FUNCNAME_NAMING, $text);
				$this->_reporter->writeError($this->lineNumber, $msg, $this->_config->getTestLevel('functionNaming'));
			}
		}
	}

	/**
	 * Check the naming of a private function.
	 *
	 * @param $text the name of the function.
	 */
	private function _checkPrivateFunctionNaming($text) {
		if ($this->_config->getTest('privateFunctionNaming')) {
			$ret = preg_match($this->_config->getTestRegExp('privateFunctionNaming'), $text);
			if (!$ret) {
				$msg = sprintf(PHPCHECKSTYLE_PRIVATE_FUNCNAME_NAMING, $text);
				$this->_reporter->writeError($this->lineNumber, $msg, $this->_config->getTestLevel('privateFunctionNaming'));
			}
		}
	}

	/**
	 * Check the naming of a class.
	 *
	 * @param $text the name of the class.
	 */
	private function _checkClassNaming($text) {
		if ($this->_config->getTest('classNaming')) {
			$ret = preg_match($this->_config->getTestRegExp('classNaming'), $text);
			if (!$ret) {
				$msg = sprintf(PHPCHECKSTYLE_CLASSNAME_NAMING, $text);
				$this->_reporter->writeError($this->lineNumber, $msg, $this->_config->getTestLevel('classNaming'));
			}
		}
	}

	/**
	 * Check the validity of a function call.
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
			if ($this->_config->getTest('checkProhibitedFunctions')) {
				if (in_array($text, $this->_prohibitedFunctions)) {
					$msg = sprintf(PHPCHECKSTYLE_PROHIBITED_FUNCTION, $text);
					$this->_reporter->writeError($this->lineNumber, $msg, $this->_config->getTestLevel('checkProhibitedFunctions'));
				}
			}

			// Detect deprecated functions
			$this->_checkDeprecation($text);

			// Detect an @ before the function call
			$this->_checkSilenced($text);

			// Detect space after function name
			if ($this->_config->getTest('noSpaceAfterFunctionName')) {
				if (!$this->tokenizer->checkNextTextToken("(")) {
					$this->_reporter->writeError($this->lineNumber, PHPCHECKSTYLE_FUNCNAME_SPACE_AFTER, $this->_config->getTestLevel('noSpaceAfterFunctionName'));
				}
			}
		}

		// Optimisation : Avoid using count/sizeof inside a loop
		if ($this->_config->getTest('functionInsideLoop')) {
			if ((strtolower($text) == 'count' || strtolower($text) == 'sizeof') && $this->_inControlStatement) {
				if ($this->_currentStatement == 'do' || $this->_currentStatement == 'while' || $this->_currentStatement == 'for' || $this->_currentStatement == 'foreach') {
					$msg = sprintf(PHPCHECKSTYLE_FUNCTION_INSIDE_LOOP, strtolower($text));
					$this->_reporter->writeError($this->lineNumber, $msg, $this->_config->getTestLevel('functionInsideLoop'));
				}
			}
		}
	}

	/**
	 * Process a control statement declaration (do/while/for/...).
	 */
	private function _processControlStatement($csText) {

		$csText = strtolower($csText);

		$this->_inControlStatement = true;
		$this->_currentStatement = $csText;

		// first token: if not one whitespace, error
		if ($this->_config->getTest('spaceAfterControlStmt')) {
			if (!$this->tokenizer->checkNextToken(T_WHITESPACE)) {
				$msg = sprintf(PHPCHECKSTYLE_SPACE_AFTER_TOKEN, $csText);
				$this->_reporter->writeError($this->lineNumber, $msg, $this->_config->getTestLevel('spaceAfterControlStmt'));
			}
		}

		// for some control structures like "else" and "do",
		// there is no statments they will be followed directly by "{"
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
				$this->_reporter->writeError($this->lineNumber, $msg, $this->_config->getTestLevel('controlStructElse'));
			}
			if (($position == 'nl') && ($previousTokenInfo->lineOffset == 0)) {
				$msg = sprintf(PHPCHECKSTYLE_CS_STMT_ON_NEW_LINE, $csText);
				$this->_reporter->writeError($this->lineNumber, $msg, $this->_config->getTestLevel('controlStructElse'));
			}
		}
	}

	/**
	 * Process the start of a control structure (do/while/...).
	 */
	private function _processControlStructureStart() {

		// check for curly braces
		if ($this->_config->getTest('controlStructOpenCurly')) {

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
				$this->_reporter->writeError($this->lineNumber, $msg, $this->_config->getTestLevel('controlStructOpenCurly'));
			}
		}

		// WARN: used for a very simple (and wrong!) do/while processing
		$this->_justAfterControlStmt = false;
	}

	/**
	 * Process the start of a function declaration.
	 */
	private function _processFunctionStart() {

		$this->_inFunction = true;
		$this->_cyclomaticComplexity = 1;
		$this->_functionLevel = $this->_levelOfNesting;
		$this->_justAfterFuncStmt = false;

		$this->_functionStartLine = $this->lineNumber;

		// Check the position of the open curly after the function declaration
		if ($this->_config->getTest('funcDefinitionOpenCurly')) {
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
				$this->_reporter->writeError($this->lineNumber, $msg, $this->_config->getTestLevel('funcDefinitionOpenCurly'));
			}
		}
	}

	/**
	 * Process the end of a function declaration.
	 */
	private function _processFunctionStop() {

		$this->_inFunction = false;

		// Check cyclomaticComplexity
		if ($this->_config->getTest('cyclomaticComplexity')) {

			$warningLevel = $this->_config->getTestProperty('cyclomaticComplexity', 'warningLevel');
			$errorLevel = $this->_config->getTestProperty('cyclomaticComplexity', 'errorLevel');

			if ($this->_cyclomaticComplexity > $warningLevel) {
				$msg = sprintf(PHPCHECKSTYLE_CYCLOMATIC_COMPLEXITY, $this->_cyclomaticComplexity);
				$this->_reporter->writeError($this->_functionStartLine, $msg, 'WARNING');
			} else if ($this->_cyclomaticComplexity > $errorLevel) {
				$msg = sprintf(PHPCHECKSTYLE_CYCLOMATIC_COMPLEXITY, $this->_cyclomaticComplexity);
				$this->_reporter->writeError($this->_functionStartLine, $msg, 'ERROR');
			}
		}

		//
		// Check that the declared parameters in the docblock match the content of the function
		//
		// If the function is not private and we check the doc
		$isPrivateExcluded = $this->_config->getTestProperty('docBlocks', 'excludePrivateMembers');
		if (!($isPrivateExcluded && $this->_isFunctionPrivate)) {

			// Check the docblock @return
			if ($this->_config->getTest('docBlocks') && $this->_config->getTestProperty('docBlocks', 'testReturn') != 'false') {

				if ($this->_functionReturns && ($this->_docblocNbReturns == 0)) {
					$this->_reporter->writeError($this->_functionStartLine, PHPCHECKSTYLE_DOCBLOCK_RETURN, $this->_config->getTestLevel('docBlocks'));
				}
			}

			// Check the docblock @param
			if ($this->_config->getTest('docBlocks') && $this->_config->getTestProperty('docBlocks', 'testParam') != 'false') {

				if ($this->_nbFunctionParameters != $this->_docblocNbParams) {
					$this->_reporter->writeError($this->_functionStartLine, PHPCHECKSTYLE_DOCBLOCK_PARAM, $this->_config->getTestLevel('docBlocks'));
				}
			}

			// Check the docblock @throw
			if ($this->_config->getTest('docBlocks') && $this->_config->getTestProperty('docBlocks', 'testThrow') != 'false') {

				if ($this->_functionThrows && ($this->_docblocNbThrows == 0)) {
					$this->_reporter->writeError($this->_functionStartLine, PHPCHECKSTYLE_DOCBLOCK_THROW, $this->_config->getTestLevel('docBlocks'));
				}
			}
		}

		$this->_docblocNbParams = 0;
		$this->_docblocNbReturns = 0;
		$this->_docblocNbThrows = 0;

		// Check the lenght of the function
		if ($this->_config->getTest('functionLength')) {

			$maxLength = $this->_config->getTestProperty('functionLength', 'maxLength');
			$functionLength = $this->lineNumber - $this->_functionStartLine;

			if ($functionLength > $maxLength) {
				$msg = sprintf(PHPCHECKSTYLE_FUNCTION_LENGTH_THROW, $functionLength);
				$this->_reporter->writeError($this->_functionStartLine, $msg, $this->_config->getTestLevel('functionLength'));
			}
		}

		// Check unused function parameters
		$this->_checkUnusedFunctionParameters();

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

		// Check if the function is private or not
		$this->_isFunctionPrivate = false;
		if ($this->tokenizer->checkPreviousValidToken(T_PRIVATE)) {
			$this->_isFunctionPrivate = true; // We are currently in a private function
			}

		// Skip until T_STRING representing the function name
		while (!$this->tokenizer->checkProvidedToken($this->token, T_STRING)) {
			$this->_moveToken();
		}

		// Extract the function name
		$functionName = $this->token[1];

		// If the function is private we add it to the list of function to use (and store the line number)
		if ($this->_isFunctionPrivate) {
			$this->_privateFunctions[$functionName] = $functionName;
			$this->_privateFunctionsStartLines[$functionName] = $this->lineNumber;
		}

		// Function is a constructor
		if ($functionName == "__construct" && $this->_config->getTest('constructorNaming') && $this->_config->getTestProperty('constructorNaming', 'naming') == 'old') {
			$msg = sprintf(PHPCHECKSTYLE_CONSTRUCTOR_NAMING, 'old style : '.$this->_currentClassname);

			$this->_reporter->writeError($this->lineNumber, $msg, $this->_config->getTestLevel('constructorNaming'));
		}

		// Special functions are not checked
		if (!in_array($functionName, $this->_specialFunctions)) {

			// Constructor
			if ($functionName == $this->_currentClassname) {

				// Function is a constructor
				if ($this->_config->getTest('constructorNaming') && $this->_config->getTestProperty('constructorNaming', 'naming') == 'new') {
					$msg = sprintf(PHPCHECKSTYLE_CONSTRUCTOR_NAMING, 'new style : __construct()');

					$this->_reporter->writeError($this->lineNumber, $msg, $this->_config->getTestLevel('constructorNaming'));
				}

			} else {
				if ($this->_isFunctionPrivate) {
					$this->_checkPrivateFunctionNaming($functionName);
				} else {
					$this->_checkFunctionNaming($functionName);
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
				if ($this->_config->getTest('defaultValuesOrder')) {
					if ($foundDefaultValues && !$hasDefaultValue) {
						$this->_reporter->writeError($this->lineNumber, PHPCHECKSTYLE_FUNC_DEFAULTVALUE_ORDER, $this->_config->getTestLevel('defaultValuesOrder'));
						break;
					}
				}
			}

			$functionTokenPosition++;

		}

		// Test for the max number of parameters
		if ($this->_config->getTest('functionMaxParameters')) {

			if ($this->_nbFunctionParameters > $this->_config->getTestProperty('functionMaxParameters', 'maxParameters')) {
				$msg = sprintf(PHPCHECKSTYLE_MAX_PARAMETERS, $this->_nbFunctionParameters);
				$this->_reporter->writeError($this->lineNumber, $msg, $this->_config->getTestLevel('functionMaxParameters'));
			}
		}
	}

	/**
	 * Process the start of a switch block.
	 */
	private function _processSwitchStart() {
		$this->_inSwitch = true;
		$this->_switchLevel = $this->_levelOfNesting;
		$this->_switchHasDefault = false;
		$this->_caseHasBreak = true;
		$this->_switchStartLine = $this->lineNumber;
	}

	/**
	 * Process the end of a switch block.
	 */
	private function _processSwitchStop() {
		$this->_inSwitch = false;

		if ($this->_config->getTest('switchNeedDefault') && !$this->_switchHasDefault) {
			$this->_reporter->writeError($this->_switchStartLine, PHPCHECKSTYLE_SWITCH_DEFAULT, $this->_config->getTestLevel('switchNeedDefault'));
		}
	}

	/**
	 * Process a case statement.
	 */
	private function _processSwitchCase() {

		// Test if the previous case had a break
		if ($this->_config->getTest('switchCaseNeedBreak') && !$this->_caseHasBreak) {
			$this->_reporter->writeError($this->_caseStartLine, PHPCHECKSTYLE_SWITCH_CASE_NEED_BREAK, $this->_config->getTestLevel('switchCaseNeedBreak'));
		}

		// If the case arrives after the default
		if ($this->_config->getTest('switchDefaultOrder') && $this->_switchHasDefault) {
			$this->_reporter->writeError($this->lineNumber, PHPCHECKSTYLE_SWITCH_DEFAULT_ORDER, $this->_config->getTestLevel('switchDefaultOrder'));
		}

		// For this break
		$this->_caseHasBreak = false;
		$this->_caseStartLine = $this->lineNumber;
	}

	/**
	 * Process a default statement.
	 */
	private function _processSwitchDefault() {
		$this->_switchHasDefault = true;

		// Test if the previous case had a break
		if ($this->_config->getTest('switchCaseNeedBreak') && !$this->_caseHasBreak) {
			$this->_reporter->writeError($this->_caseStartLine, PHPCHECKSTYLE_SWITCH_CASE_NEED_BREAK, $this->_config->getTestLevel('switchCaseNeedBreak'));
		}
	}

	/**
	 * Process a break statement.
	 */
	private function _processSwitchBreak() {
		$this->_caseHasBreak = true;
	}

	/**
	 * Process a class declaration statement.
	 */
	private function _processClassStatement() {

		$this->_ncssTotalClasses++;
		$this->_ncssFileClasses++;

		// Test if there is more than one class per file
		if ($this->_config->getTest('oneClassPerFile') && $this->_ncssFileClasses > 1) {
			$this->_reporter->writeError($this->lineNumber, PHPCHECKSTYLE_ONE_CLASS_PER_FILE, $this->_config->getTestLevel('oneClassPerFile'));
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

		// class name has to start with uppercase
		$classname = $this->token[1];
		$this->_currentClassname = $classname;

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
		if ($this->_config->getTest('checkEmptyBlock') && $this->_currentStatement) {

			if ($this->tokenizer->checkNextValidTextToken("}")) {
				$msg = sprintf(PHPCHECKSTYLE_EMPTY_BLOCK, $this->_currentStatement);
				$this->_reporter->writeError($this->lineNumber, $msg, $this->_config->getTestLevel('checkEmptyBlock'));
			}
		}
	}

	/**
	 * Check for inner assignments.
	 *
	 * This function is launched when the current token is = (assignment).
	 */
	private function _checkInnerAssignment() {

		// If the test if active and we are inside a control statement
		if ($this->_config->getTest('checkInnerAssignment') && $this->_inControlStatement) {

			// If the control statement is not listed as an exception
			$exceptions = $this->_config->getTestExceptions('checkInnerAssignment');
			if (empty($exceptions) || !in_array($this->_currentStatement, $exceptions)) {
				$this->_reporter->writeError($this->lineNumber, PHPCHECKSTYLE_INSIDE_ASSIGNMENT, $this->_config->getTestLevel('checkInnerAssignment'));

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
		if ($this->_config->getTest('checkEmptyStatement') && $this->_currentStatement) {

			if ($this->tokenizer->checkNextValidTextToken(";")) {
				$this->_reporter->writeError($this->lineNumber, PHPCHECKSTYLE_EMPTY_STATEMENT, $this->_config->getTestLevel('checkEmptyStatement'));
			}
		}
	}

	/**
	 * Check for unused functions.
	 *
	 * This function is launched at the end of a file.
	 */
	private function _checkUnusedPrivateFunctions() {

		if ($this->_config->getTest('checkUnusedPrivateFunctions')) {

			// We make a diff between the list of declared private functions and the list of called functions.
			// This is a very simple and approximative test, we don't test that the called function is from the good class.
			// The usedFunctions array contains a lot of false positives
			$uncalledFunctions = array_diff($this->_privateFunctions, $this->_usedFunctions);

			foreach ($uncalledFunctions as $uncalledFunction) {
				$msg = sprintf(PHPCHECKSTYLE_UNUSED_PRIVATE_FUNCTION, $uncalledFunction);
				$this->_reporter->writeError($this->_privateFunctionsStartLines[$uncalledFunction], $msg, $this->_config->getTestLevel('checkUnusedPrivateFunctions'));
			}
		}
	}

	/**
	 * Check for unused variables in the file.
	 *
	 * This function is launched at the end of a file.
	 */
	private function _checkUnusedVariables() {

		if ($this->_config->getTest('checkUnusedVariables')) {

			foreach ($this->_variables as $variableName => $value) {
				if (($value != "used") && !($this->_isClass || $this->_isView)) {
					$msg = sprintf(PHPCHECKSTYLE_UNUSED_VARIABLE, $variableName);
					$this->_reporter->writeError($value, $msg, $this->_config->getTestLevel('checkUnusedVariables'));
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

		if ($this->_config->getTest('checkUnusedFunctionParameters')) {

			foreach ($this->_functionParameters as $variableName => $value) {
				if ($value != "used") {
					$msg = sprintf(PHPCHECKSTYLE_UNUSED_FUNCTION_PARAMETER, $variableName);
					$this->_reporter->writeError($this->_functionStartLine, $msg, $this->_config->getTestLevel('checkUnusedFunctionParameters'));
				}
			}
		}
	}

	/**
	 * Check the variable use.
	 *
	 * This function is launched when the current token is T_VARIABLE
	 */
	private function _processVariable($text) {

		// Check the variable naming
		if (!in_array($text, $this->_systemVariables)) {
			$this->_checkVariableNaming($text);
		}

		// Check if the variable is a function parameter
		if (!empty($this->_functionParameters[$text]) && $this->_inFunction) {

			$this->_functionParameters[$text] = "used";

		} else if (!$this->_inFunctionStatement) {

			// Global variable
			$pos = $this->tokenizer->getCurrentPosition();
			$nextTokenInfo = $this->tokenizer->peekNextValidToken($pos);

			// if the next token is an equal, we suppose that this is an affectation
			$isAffectation = $this->tokenizer->checkProvidedText($nextTokenInfo->token, "=");

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
	 * Check the return token.
	 *
	 * This function is launched when the current token is T_RETURN
	 */
	private function _processReturn() {
		// Remember that the current function does return something (for PHPDoc)
		$this->_functionReturns = true;

		// Search for unused code
		if ($this->_config->getTest('checkUnusedCode')) {

			// Look ahead
			$stop = false;
			$nbInstructions = 0;
			$position = $this->tokenizer->getCurrentPosition();
			while (!$stop) {
				$token = $this->tokenizer->peekTokenAt($position);
				if (is_string($token)) {
					if ($token == ";") {
						$nbInstructions++;
						if ($nbInstructions > 1) {
							// more than 1 instruction after the return and before the closing of the statement
							$this->_reporter->writeError($this->lineNumber, PHPCHECKSTYLE_UNUSED_CODE, $this->_config->getTestLevel('checkUnusedCode'));
							$stop = true;
						}
					} else if ($token == "}") {
						$stop = true;
					}
				}
				$position++;
			}
		}
	}

	/**
	 * Check for heredoc syntax.
	 *
	 * This function is launched when the current token is T_START_HEREDOC
	 */
	private function _checkHeredoc() {
		if ($this->_config->getTest('checkHeredoc')) {
			$this->_reporter->writeError($this->lineNumber, PHPCHECKSTYLE_HEREDOC, $this->_config->getTestLevel('checkHeredoc'));
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
		if ($this->_config->getTest('checkWhiteSpaceBefore')) {

			$exceptions = $this->_config->getTestExceptions('checkWhiteSpaceBefore');

			if (empty($exceptions) || !in_array($text, $exceptions)) {

				if (!$this->tokenizer->checkProvidedToken($this->prvsToken, T_WHITESPACE)) {
					$msg = sprintf(PHPCHECKSTYLE_SPACE_BEFORE_TOKEN, $text);
					$this->_reporter->writeError($this->lineNumber, $msg, $this->_config->getTestLevel('checkWhiteSpaceBefore'));
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
		if ($this->_config->getTest('noSpaceBeforeToken')) {

			$exceptions = $this->_config->getTestExceptions('noSpaceBeforeToken');
			if (empty($exceptions) || !in_array($text, $exceptions)) {

				if ($this->tokenizer->checkProvidedToken($this->prvsToken, T_WHITESPACE)) {
					$msg = sprintf(PHPCHECKSTYLE_NO_SPACE_BEFORE_TOKEN, $text);
					$this->_reporter->writeError($this->lineNumber, $msg, $this->_config->getTestLevel('noSpaceBeforeToken'));
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
		if ($this->_config->getTest('checkWhiteSpaceAfter')) {

			$exceptions = $this->_config->getTestExceptions('checkWhiteSpaceAfter');
			if (empty($exceptions) || !in_array($text, $exceptions)) {

				if (!$this->tokenizer->checkNextToken(T_WHITESPACE)) {
					// In case of new line or a php closing tag it's OK
					if (!($this->tokenizer->checkNextToken(T_NEW_LINE) || $this->tokenizer->checkNextToken(T_CLOSE_TAG))) {
						$msg = sprintf(PHPCHECKSTYLE_SPACE_AFTER_TOKEN, $text);
						$this->_reporter->writeError($this->lineNumber, $msg, $this->_config->getTestLevel('checkWhiteSpaceAfter'));
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
		if ($this->_config->getTest('noSpaceAfterToken')) {

			$exceptions = $this->_config->getTestExceptions('noSpaceAfterToken');
			if (empty($exceptions) || !in_array($text, $exceptions)) {

				if ($this->tokenizer->checkNextToken(T_WHITESPACE)) {
					$msg = sprintf(PHPCHECKSTYLE_NO_SPACE_AFTER_TOKEN, $text);
					$this->_reporter->writeError($this->lineNumber, $msg, $this->_config->getTestLevel('noSpaceAfterToken'));
				}
			}
		}
	}

	/**
	 * Avoid using unary operators (++ or --) inside a control statement.
	 */
	private function _checkUnaryOperator() {
		if ($this->_config->getTest('checkUnaryOperator')) {

			// If the control statement is not listed as an exception
			$exceptions = $this->_config->getTestExceptions('checkUnaryOperator');

			if (empty($exceptions) || !in_array($this->_currentStatement, $exceptions) || $this->_inArrayStatement) {
				// And if we are currently in a control statement or an array statement
				if ($this->_inControlStatement || $this->_inArrayStatement) {
					$this->_reporter->writeError($this->lineNumber, PHPCHECKSTYLE_UNARY_OPERATOR, $this->_config->getTestLevel('checkUnaryOperator'));
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

		$isHtml = $this->tokenizer->checkProvidedToken($this->token, T_INLINE_HTML);

		// Test the lenght of the line
		if (!$isHtml && $this->_config->getTest('lineLength')) {
			$this->_checkLargeLine();
		}
	}

	/**
	 * Check if the current line exceeds the maxLineLength allowed.
	 */
	private function _checkLargeLine() {

		if ($this->_config->getTest('lineLength')) {

			// Comments are ignored
			if (!($this->tokenizer->checkProvidedToken($this->token, T_COMMENT) || $this->tokenizer->checkProvidedToken($this->token, T_ML_COMMENT) || $this->tokenizer->checkProvidedToken($this->token, T_DOC_COMMENT))) {

				$text = $this->tokenizer->extractTokenText($this->token);
				$text = trim($text);
				$maxlen = $this->_config->getTestProperty('lineLength', 'maxLineLength');
				$msg = sprintf(PHPCHECKSTYLE_LONG_LINE, $maxlen);
				if (strlen($text) > $maxlen) {
					$this->_reporter->writeError($this->lineNumber, $msg, $this->_config->getTestLevel('lineLength'));
				}
			}
		}
	}

	/**
	 * Only checks for presence of tab in the whitespace character string
	 *
	 * @param String $ws
	 */
	private function _checkIndentation($ws) {
		if ($this->_config->getTest('noTabs')) {
			$tabfound = preg_match("/\t/", $ws);
			if ($tabfound) {
				$this->_reporter->writeError($this->lineNumber, PHPCHECKSTYLE_TAB_IN_LINE, $this->_config->getTestLevel('noTabs'));
			}
		}
	}

	/**
	 * Checks if the block of code need braces.
	 *
	 * This function is called we the current token is ) and we are in a control statement.
	 */
	private function _checkNeedBraces() {
		if ($this->_config->getTest('needBraces')) {

			$stmt = strtolower($this->_currentStatement);
			if ($stmt == "if" || $stmt == "else" || $stmt == "elseif" || $stmt == "do" || $stmt == "while" || $stmt == "for" || $stmt == "foreach") {
				if (!$this->tokenizer->checkNextValidTextToken("{")) {
					$this->_reporter->writeError($this->lineNumber, PHPCHECKSTYLE_NEED_BRACES, $this->_config->getTestLevel('needBraces'));
				}
			}

		}
	}

	/**
	 * Process a comment.
	 *
	 * @param $tol the token
	 * @param $text the text of the comment
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
		}

		// Manage new lines inside commments
		$subToken = strtok($text, PHP_EOL);
		while ($subToken !== false) {

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
			$subToken = strtok(PHP_EOL);
		}
		$this->lineNumber--; // One end of line is already counted

		// Check if the comment starts with '#'
		if ($this->_config->getTest("noShellComments")) {
			$s = strpos($text, '#');
			if ($s === 0) {
				$this->_reporter->writeError($this->lineNumber, PHPCHECKSTYLE_NO_SHELL_COMMENTS, $this->_config->getTestLevel('noShellComments'));
			}
		}

		// Check if the comment contains a TODO
		if ($this->_config->getTest("showTODOs")) {
			$s = strpos($text, 'TODO');
			if ($s != FALSE) {
				$msg = sprintf(PHPCHECKSTYLE_TODO, substr($text, strpos($text, 'TODO') + 4));
				$this->_reporter->writeError($this->lineNumber, $msg, $this->_config->getTestLevel('showTODOs'));
			}
		}

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
	 * This, of course, assumes that the function or the class has to be
	 * immediately preceded by docblock. Even regular comments are not
	 * allowed, which I think is okay.
	 *
	 * @param $isPrivate specify whether private members are excluded from test
	 * @return true is docblock is found
	 */
	private function _checkDocExists() {

		// current token = the token after T_CLASS or T_FUNCTION
		//
		// token positions:
		//  .  curToken - 1 = T_CLASS/T_FUNCTION
		//  .  curToken - 2 = whitespace before T_CLASS/T_FUNCTION
		//  .  curToken - 3 = T_ABSTRACT/T_PUBLIC/T_PROTECTED/T_PRIVATE/T_STATIC
		//                    or T_DOC_COMMENT, if it is present
		//
		if ($this->_config->getTest('docBlocks')) {

			$isPrivate = $this->_config->getTestProperty('docBlocks', 'excludePrivateMembers');

			// Locate the function or class token
			$functionTokenPosition = $this->tokenizer->getCurrentPosition();
			while (true) {
				$functionToken = $this->tokenizer->peekTokenAt($functionTokenPosition);
				if ($this->tokenizer->checkProvidedToken($functionToken, T_FUNCTION) || $this->tokenizer->checkProvidedToken($functionToken, T_CLASS)) {
					break;
				}

				$functionTokenPosition--;
			}

			$found = false;
			$docTokenPosition = $functionTokenPosition - 1;

			// Go backward and look for a T_DOC_COMMENT
			while (true) {
				$docToken = $this->tokenizer->peekTokenAt($docTokenPosition);

				if (is_array($docToken)) {

					if ($this->tokenizer->checkProvidedToken($docToken, T_STATIC) || $this->tokenizer->checkProvidedToken($docToken, T_ABSTRACT) || $this->tokenizer->checkProvidedToken($docToken, T_PROTECTED) || $this->tokenizer->checkProvidedToken($docToken, T_PUBLIC) || $this->tokenizer->checkProvidedToken($docToken, T_WHITESPACE) || $this->tokenizer->checkProvidedToken($docToken, T_COMMENT) || $this->tokenizer->checkProvidedToken($docToken, T_ML_COMMENT) || $this->tokenizer->checkProvidedToken($docToken, T_NEW_LINE)) {
						// All these tokens are ignored
						} else if ($this->tokenizer->checkProvidedToken($docToken, T_PRIVATE)) {
						// We don't care if we find anything, private functions are not tested
						if ($isPrivate) {
							$found = true;
						}
						break;
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

			if (!$found) {
				$this->_reporter->writeError($this->lineNumber, PHPCHECKSTYLE_MISSING_DOCBLOCK, $this->_config->getTestLevel('docBlocks'));
			}

		}
	}

	/**
	 * Display the current branching stack?
	 */
	private function _dumpStack() {
		foreach ($this->_branchingStack as $item) {
			echo $item."->";
		}
		echo PHP_EOL;
	}

	/**
	 * Check for silenced call to functons.
	 *
	 * @param String $text The text of the token to test
	 */
	private function _checkSilenced($text) {
		if ($this->_config->getTest('checkSilencedError')) {

			$exceptions = $this->_config->getTestExceptions('checkSilencedError');
			if (empty($exceptions) || !in_array($text, $exceptions)) {
				if ($this->prvsToken == "@") {
					$this->_reporter->writeError($this->lineNumber, PHPCHECKSTYLE_SILENCED_ERROR, $this->_config->getTestLevel('checkSilencedError'));
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
		if ($this->_config->getTest('checkDeprecation')) {
			if (array_key_exists($text, $this->_deprecatedFunctions)) {
				$msg = sprintf(PHPCHECKSTYLE_DEPRECATED_FUNCTION, $this->_deprecatedFunctions[$text]['old'], $this->_deprecatedFunctions[$text]['version'], $this->_deprecatedFunctions[$text]['new']);
				$this->_reporter->writeError($this->lineNumber, $msg, $this->_config->getTestLevel('checkDeprecation'));
			}
		}
	}

}
