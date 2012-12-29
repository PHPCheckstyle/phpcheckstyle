<?php
if (!defined("PHPCHECKSTYLE_HOME_DIR")) {
	define("PHPCHECKSTYLE_HOME_DIR", dirname(__FILE__)."/..");
}
require_once PHPCHECKSTYLE_HOME_DIR."/src/TokenInfo.php";

/**
 * Lexical Analysis.
 *
 * Class that stores the tokens for a particular class and provide
 * utility functions like getting the next/previous token,
 * checking whether the token is of particular type etc.
 *
 * Based on the internal PHP tokenizer but separate the NEW_LINE and the TAB tokens.
 *
 * @see http://www.php.net/manual/en/tokens.php
 * @author Hari Kodungallur <hkodungallur@spikesource.com>
 */
class Tokenizer {

	/**
	 * The array of tokens in a file.
	 * @var Array[TokenInfo]
	 */
	private $tokens;

	/**
	 * Position of the index in the current file.
	 * @var Integer
	 */
	private $index = 0;

	/**
	 * Constructor
	 */
	public function Tokenizer() {
		$this->reset();
	}

	/**
	 * Tokenizes the input php file and stores all the tokens in the
	 * $this->tokens variable.
	 *
	 * @param String $filename the line where the token is found
	 */
	public function tokenize($filename) {
		$contents = "";
		if (filesize($filename)) {
			$fp = fopen($filename, "rb");
			$contents = fread($fp, filesize($filename));
			fclose($fp);
		}
		$this->tokens = $this->_getAllTokens($contents);
	}

	/**
	 * Dump the tokens of the file.
	 *
	 * @return String
	 */
	public function dumpTokens() {
		$result = "";
		foreach ($this->tokens as $token) {
			$result .= $token->toString().PHP_EOL;
		}
		return $result;
	}

	/**
	 * Gets the next token.
	 *
	 * In the process moves the index to the next position.
	 *
	 * @return TokenInfo
	 */
	public function getNextToken() {
		if ($this->index < (count($this->tokens) - 1)) {

			// Increment the index
			$this->index++;

			// Return the new token
			return $this->tokens[$this->index];
		} else {
			return false;
		}
	}

	/**
	 * Gets the current token.
	 *
	 * @return TokenInfo
	 */
	public function getCurrentToken() {
		return $this->tokens[$this->index];
	}

	/**
	 * Get the token at a given position.
	 *
	 * @param Integer $position the position of the token
	 * @return TokenInfo the token found
	 */
	public function peekTokenAt($position) {
		if ($position < count($this->tokens)) {
			return $this->tokens[$position];
		} else {
			return null;
		}
	}

	/**
	 * Gives the current position in the tokenizer.
	 *
	 * @return current position of the Tokenizer
	 */
	public function getCurrentPosition() {
		return $this->index;
	}

	/**
	 * Returns the next token without moving
	 * the index.
	 *
	 * @return TokenInfo if the token is found (and update the line value)
	 */
	public function peekNextToken() {
		if ($this->index < (count($this->tokens) - 1)) {
			return $this->tokens[$this->index + 1];
		} else {
			return null;
		}
	}

	/**
	 * Peeks at the previous token. That is it returns the previous token
	 * without moving the index.
	 *
	 * @return TokenInfo
	 */
	public function peekPrvsToken() {
		if ($this->index > 0) {
			return $this->tokens[$this->index - 1];
		} else {
			return null;
		}
	}

	/**
	 * Peeks at the next valid token.
	 * A valid token is one that is neither a whitespace or a comment
	 *
	 * @param Integer $startPos the start position for the search (if the item on this position is valid, it will be returned)
	 * @param Boolean $stopOnNewLine Indicate if we need to stop when we meet a new line
	 * @return TokenInfo the info about the token found
	 */
	public function peekNextValidToken($startPos = null, $stopOnNewLine = false) {

		// define the start position
		$pos = $this->getCurrentPosition() + 1;  // defaut position for the search
		if ($startPos != null) {
			$pos = $startPos; // if defined, set the start position
		}

		// search for the next valid token
		$token = null;
		$nbTokens = count($this->tokens);
		while ($pos < $nbTokens) {
			$token = $this->tokens[$pos];
			$pos++;

			if ($token->id == T_WHITESPACE || $token->id == T_TAB || $token->id == T_COMMENT || $token->id == T_ML_COMMENT || $token->id == T_DOC_COMMENT) {
				continue;
			} else if ($token->id == T_NEW_LINE) {
				if ($stopOnNewLine) {
					break;
				} else {
					continue;
				}
			} else {
				break;
			}

		}

		return $token;

	}

	/**
	 * Peeks at the previous valid token.
	 * A valid token is one that is neither a whitespace or a comment
	 *
	 * @return TokenInfo the info about the token found
	 */
	public function peekPrvsValidToken() {
		$pos = $this->index - 1;

		$token = null;
		while ($pos > 0) {

			$token = $this->tokens[$pos];
			$pos--;

			if ($token->id == T_WHITESPACE || $token->id == T_TAB || $token->id == T_COMMENT || $token->id == T_ML_COMMENT || $token->id == T_DOC_COMMENT) {
				continue;
			} else if ($token->id == T_NEW_LINE) {
				continue;
			} else {
				break;
			}
		}

		return $token;

	}

	/**
	 * Resets all local variables
	 *
	 * @return
	 * @access public
	 */
	public function reset() {
		$this->index = 0;
		$this->tokens = array();
	}

	/**
	 * Check if a token is equal to a given token ID
	 *
	 * @param TokenInfo $token the token to test
	 * @param Integer $id the token ID we're looking for
	 * @param String $text (optional) the text we're looking for
	 * @return Boolean true if the token correspond
	 */
	public function checkToken($token, $id, $text = false) {
		$result = false;
		if ($token->id == $id) {
			if ($text) {
				if ($token->text == $text) {
					$result = true;
				} else {
					$result = false;
				}
			} else {
				$result = true;
			}

		}
		return $result;
	}

	/**
	 * Check if the previous valid token (ignoring whitespace) correspond to the specified token.
	 *
	 * @param Integer $id the token ID we're looking for
	 * @param String $text (optional) the text we're looking for
	 * @return Boolean true if the token is found
	 */
	public function checkPreviousValidToken($id, $text = false) {
		$token = $this->peekPrvsValidToken();
		return $this->checkToken($token, $id, $text);
	}

	/**
	 * Check if the previous valid token (ignoring whitespace) correspond to the specified token.
	 *
	 * @param Integer $id the token ID we're looking for
	 * @param String $text (optional) the text we're looking for
	 * @return Boolean true if the token is found
	 */
	public function checkPreviousToken($id, $text = false) {
		$token = $this->peekPrvsToken();
		return $this->checkToken($token, $id, $text);
	}

	/**
	 * Check if a the next token exists (and if its value correspond to what is expected).
	 *
	 * @param Integer $id the token we're looking for
	 * @param String $text (optional) the text we're looking for
	 * @return Boolean true if the token is found
	 */
	public function checkNextToken($id, $text = false) {
		$token = $this->peekNextToken();
		return $this->checkToken($token, $id, $text);
	}

	/**
	 * Check if a the next token exists (and if its value correspond to what is expected).
	 *
	 * @param Integer $id the token we're looking for
	 * @param String $text (optional) the text we're looking for
	 * @return Boolean true if the token is found
	 */
	public function checkCurrentToken($id, $text = false) {
		$token = $this->getCurrentToken();
		return $this->checkToken($token, $id, $text);
	}

	/**
	 * Find the next position of the string.
	 *
	 * @param String $text the text we're looking for
	 * @param Integer $apos the position to start from (by defaut will use current position)
	 * @return Integer the position, null if not found
	 */
	public function findNextStringPosition($text, $apos = null) {

		if ($apos == null) {
			$pos = $this->getCurrentPosition();
		} else {
			$pos = $apos;
		}
		$pos += 1; // Start from the token following the current position

		$nbTokens = count($this->tokens);
		while  ($pos < $nbTokens) {
			$token = $this->tokens[$pos];

			if ($text == $token->text) {
				return $pos;
			}

			$pos++;
		}

		return null;
	}

	/**
	 * Check if the next valid token (ignoring whitespaces) correspond to the specified token.
	 *
	 * @param Integer $id the id of the token we're looking for
	 * @param String $text the text we're looking for
	 * @param Integer $startPos the start position
	 * @return true if the token is found
	 */
	public function checkNextValidToken($id, $text = false, $startPos = null) {

		$tokenInfo = $this->peekNextValidToken($startPos);

		if ($tokenInfo != null) {
			return $this->checkToken($tokenInfo, $id, $text);
		} else {
			return false;
		}
	}

	/**
	 * Tokenize a string and separate the newline token.
	 *
	 * Found here : http://php.net/manual/function.token-get-all.php
	 *
	 * @param String $source The source code to analyse
	 * @return an array of tokens
	 */
	private function _getAllTokens($source) {
		$newTokens = array();

		$lineNumber = 1;
		$position = 0;

		// Get the tokens
		$tokens = token_get_all($source);

		// Split newlines into their own tokens
		foreach ($tokens as $token) {

			$tokenID = is_array($token) ? $token[0] : T_UNKNOWN;
			$tokenText = is_array($token) ? $token[1] : $token;

			// Split the data up by newlines
			// To correctly handle T_NEW_LINE inside comments and HTML
			$splitData = preg_split('#(\r\n|\n|\r)#', $tokenText, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			foreach ($splitData as $data) {

				$tokenInfo = new TokenInfo();
				$tokenInfo->text = $data;
				$tokenInfo->position = $position;
				$tokenInfo->line = $lineNumber;

				if ($data == "\r\n" || $data == "\n" || $data == "\r") {
					// This is a new line token
					$tokenInfo->id = T_NEW_LINE;
					$lineNumber++;
				} else if ($data == "\t") {
					// This is a tab token
					$tokenInfo->id = T_TAB;
				} else {
					// Any other token
					$tokenInfo->id = $tokenID;
				}

				$position++;

				// Added detections
				if ($tokenInfo->id == T_UNKNOWN) {
					switch ($tokenInfo->text) {
						case ";" :
							$tokenInfo->id = T_SEMICOLON;
							break;
						case "{":
							$tokenInfo->id = T_BRACES_OPEN;
							break;
						case "}":
							$tokenInfo->id = T_BRACES_CLOSE;
							break;
						case "(":
							$tokenInfo->id = T_PARENTHESIS_OPEN;
							break;
						case ")":
							$tokenInfo->id = T_PARENTHESIS_CLOSE;
							break;
						case ",":
							$tokenInfo->id = T_COMMA;
							break;
						case "=":
							$tokenInfo->id = T_EQUAL;
							break;
						case ".":
							$tokenInfo->id = T_CONCAT;
							break;
						case ":" :
							$tokenInfo->id = T_COLON;
							break;
						case "-" :
							$tokenInfo->id = T_MINUS;
							break;
						case "+" :
							$tokenInfo->id = T_PLUS;
							break;
						case ">" :
							$tokenInfo->id = T_IS_GREATER;
							break;
						case "<" :
							$tokenInfo->id = T_IS_SMALLER;
							break;
						case "*" :
							$tokenInfo->id = T_MULTIPLY;
							break;
						case "/" :
							$tokenInfo->id = T_DIVIDE;
							break;
						case "?" :
							$tokenInfo->id = T_QUESTION_MARK;
							break;
						case "%" :
							$tokenInfo->id = T_MODULO;
							break;
						case "!" :
							$tokenInfo->id = T_EXCLAMATION_MARK;
							break;
						case "&" :
							$tokenInfo->id = T_AMPERSAND;
							break;
						case "[" :
							$tokenInfo->id = T_SQUARE_BRACKET_OPEN;
							break;
						case "]" :
							$tokenInfo->id = T_SQUARE_BRACKET_CLOSE;
							break;
						case "@" :
							$tokenInfo->id = T_AROBAS;
							break;
						default:
					}
				}

				$newTokens[] = $tokenInfo;
			}
		}

		return $newTokens;
	}


	/**
	 * Find the position of the closing parenthesis corresponding to the current position opening parenthesis.
	 *
	 * @param Integer $startPos
	 * @return Integer $closing position
	 */
	public function findClosingParenthesisPosition($startPos) {

		// Find the opening parenthesis after current position
		$pos = $this->findNextStringPosition('(', $startPos);
		$parenthesisCount = 1;

		$pos += 1; // Skip the opening  parenthesis

		$nbTokens = count($this->tokens);
		while ($parenthesisCount > 0 && $pos < $nbTokens) {
			// Look for the next token
			$token = $this->peekTokenAt($pos);

			// Increment or decrement the parenthesis count
			if ($token->id == T_PARENTHESIS_OPEN) {
				$parenthesisCount += 1;
			} else if ($token->id == T_PARENTHESIS_CLOSE) {
				$parenthesisCount -= 1;
			}

			// Increment the position
			$pos += 1;
		}

		return $pos;
	}

	/**
	 * Checks if a token is in the type of token list.
	 *
	 * @param TokenInfo $tokenToCheck 	the token to check.
	 * @param Array[Integer] $tokenList 		an array of token ids, e.g. T_NEW_LINE, T_DOC_COMMENT, etc.
	 * @return Boolean true if the token is found, false if it is not.
	 */
	public function isTokenInList($tokenToCheck, $tokenList) {
		foreach ($tokenList as $tokenInList) {
			if ($this->checkToken($tokenToCheck, $tokenInList)) {
				return true;
			}
		}
		return false;
	}


}
