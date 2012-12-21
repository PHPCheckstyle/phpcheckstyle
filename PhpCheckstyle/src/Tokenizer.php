<?php
if (!defined("T_ML_COMMENT")) {
	define("T_ML_COMMENT", T_COMMENT);
}

define('T_NEW_LINE', -1);
define('T_TAB', -2);

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
	public function getToken() {
		return $this->tokens[$this->index];
	}

	/**
	 * Peeks the token at a given position.
	 *
	 * @param Integer $position the position of the token
	 * @return the token found
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
	 * Peeks the next token, i.e., returns the next token without moving
	 * the index.
	 *
	 * @return true if the token is found (and update the line value)
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
		$ret = false;
		$lineOffset = 0;
		$pos = $this->getCurrentPosition() + 1;  // defaut position for the search
		if ($startPos != null) {
			$pos = $startPos; // if defined, set the start position
		}
		while ($pos < count($this->tokens)) {
			$ret = $this->tokens[$pos];
			$pos++;
			if (is_array($ret)) {
				list($k, $v) = $ret;
				if ($k == T_WHITESPACE || $k == T_TAB || $k == T_COMMENT || $k == T_ML_COMMENT || $k == T_DOC_COMMENT) {
					continue;
				} else if ($k == T_NEW_LINE) {
					$lineOffset++; // increment the line number when a new line is found
					if ($stopOnNewLine) {
						break;
					} else {
						continue;
					}
				} else {
					break;
				}
			} else {
				break;
			}
		}
		if ($ret) {
			$result = new TokenInfo();
			$result->token = $ret;
			$result->position = $pos;
			$result->lineOffset = $lineOffset;
			return $result;
		} else {
			return null;
		}
	}

	/**
	 * Peeks at the previous valid token.
	 * A valid token is one that is neither a whitespace or a comment
	 *
	 * @return TokenInfo the info about the token found
	 */
	public function peekPrvsValidToken() {
		$ret = false;
		$tmpTokenNumber = $this->index - 1;
		$lineOffset = 0;
		while ($tmpTokenNumber > 0) {
			$ret = $this->tokens[$tmpTokenNumber];
			$tmpTokenNumber--;
			if (is_array($ret)) {
				list($k, $v) = $ret;
				if ($k == T_WHITESPACE || $k == T_TAB || $k == T_COMMENT || $k == T_ML_COMMENT || $k == T_DOC_COMMENT) {
					continue;
				} else if ($k == T_NEW_LINE) {
					$lineOffset--; // decrement the line number when a new line is found
					continue;
				} else {
					break;
				}
			} else {
				break;
			}
		}
		if ($ret) {
			$result = new TokenInfo();
			$result->token = $ret;
			$result->position = $tmpTokenNumber;
			$result->lineOffset = $lineOffset;
			return $result;
		} else {
			return null;
		}
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
	 * @param Array $token the token to test
	 * @param Integer $value the token ID we're looking for
	 * @param String $text (optional) the text we're looking for
	 * @return true if the token correspond
	 */
	public function checkToken($token, $value, $text = false) {
		$ret = false;
		if (is_array($token)) {
			list($k, $v) = $token;
			if ($k == $value) {
				if ($text) {
					if ($v == $text) {
						$ret = true;
					}
				} else {
					$ret = true;
				}
			}
		}
		return $ret;
	}

	/**
	 * Check if the previous valid token (ignoring whitespace) correspond to the specified token.
	 *
	 * @param Array $value the token ID we're looking for
	 * @param String $text (optional) the text we're looking for
	 * @return true if the token is found
	 */
	public function checkPreviousValidToken($value, $text = false) {
		$tokenInfo = $this->peekPrvsValidToken();

		return $this->checkToken($tokenInfo->token, $value, $text);
	}

	/**
	 * Check if the previous valid token (ignoring whitespace) correspond to the specified token.
	 *
	 * @param Array $value the token ID we're looking for
	 * @param String $text (optional) the text we're looking for
	 * @return true if the token is found
	 */
	public function checkPreviousToken($value, $text = false) {
		$token = $this->peekPrvsToken();

		return $this->checkToken($token, $value, $text);
	}

	/**
	 * Check if a the next token exists (and if its value correspond to what is expected).
	 *
	 * @param Integer $value the token we're looking for
	 * @param String $text (optional) the text we're looking for
	 * @return true if the token is found
	 */
	public function checkNextToken($value, $text = false) {
		$token = $this->peekNextToken();

		return $this->checkToken($token, $value, $text);
	}

	/**
	 * Check if a the next token exists (and if its value correspond to what is expected).
	 *
	 * @param Integer $value the token we're looking for
	 * @param String $text (optional) the text we're looking for
	 * @return true if the token is found
	 */
	public function checkCurrentToken($value, $text = false) {
		$token = $this->getToken();

		return $this->checkToken($token, $value, $text);
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

		while  ($pos < count($this->tokens)) {
			$token = $this->tokens[$pos];

			if ($text == $this->extractTokenText($token)) {
				return $pos;
			}

			$pos++;
		}

		return null;
	}

	/**
	 * Check if the next token (including whitespaces) correspond to the text.
	 *
	 * @param String $text the text we're looking for
	 * @return true if the token is found
	 */
	public function checkNextTextToken($text) {
		$token = $this->peekNextToken();

		return $this->checkText($token, $text);
	}

	/**
	 * Check if the next valid token (ignoring whitespaces) correspond to the text.
	 *
	 * @param String $text the text we're looking for
	 * @param Integer $startPos the start position
	 * @return true if the token is found
	 */
	public function checkNextValidTextToken($text, $startPos = null) {
		$tokenInfo = $this->peekNextValidToken($startPos);
		if ($tokenInfo != null) {
			return $this->checkText($tokenInfo->token, $text);
		} else {
			return false;
		}
	}

	/**
	 * Check if the next valid token (ignoring whitespaces) correspond to the specified token.
	 *
	 * @param Integer $value the value of the token we're looking for
	 * @param String $text the text we're looking for
	 * @param Integer $startPos the start position
	 * @return true if the token is found
	 */
	public function checkNextValidToken($value, $text = false, $startPos = null) {
		$tokenInfo = $this->peekNextValidToken($startPos);

		if ($tokenInfo != null) {
			return $this->checkToken($tokenInfo->token, $value, $text);
		} else {
			return false;
		}
	}

	/**
	 * Check if the token correspond to a given text
	 *
	 * @param Integer $token the token
	 * @param String $text the text
	 * @return true if the token contains the text
	 */
	public function checkText($token, $text) {
		$ret = false;
		if (is_string($token)) {
			if ($token == $text) {
				$ret = true;
			}
		}
		return $ret;
	}

	/**
	 * Extract the text contained in a token.
	 *
	 * @param Integer $token the token
	 * @return the text content of the token
	 */
	public function extractTokenText($token) {
		$ret = $token;
		if (is_array($token)) {
			$ret = $token[1];
		}
		return $ret;
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

		// Get the tokens
		$tokens = token_get_all($source);

		// Split newlines into their own tokens
		foreach ($tokens as $token) {
			$tokenName = is_array($token) ? $token[0] : null;
			$tokenData = is_array($token) ? $token[1] : $token;

			// Do not split encapsed strings or multiline comments
			if ($tokenName == T_CONSTANT_ENCAPSED_STRING || substr($tokenData, 0, 2) == '/*') {
				$newTokens[] = array($tokenName, $tokenData);
				continue;
			}

			// Split the data up by newlines
			$splitData = preg_split('#(\r\n|\n|\r)#', $tokenData, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

			foreach ($splitData as $data) {

				if ($data == "\r\n" || $data == "\n" || $data == "\r") {
					// This is a new line token
					$newTokens[] = array(T_NEW_LINE, $data);
				} else if ($data == "\t") {
					// This is a new line token
					$newTokens[] = array(T_TAB, $data);
				} else {
					// Add the token under the original token name
					$newTokens[] = is_array($token) ? array($tokenName, $data) : $data;
				}
			}
		}

		return $newTokens;
	}

	/**
	 * Return the name of a token, including the NEW_LINE one.
	 *
	 * @param Integer $token a token
	 * @return the name of the token
	 */
	public function getTokenName($token) {
		if ($token === T_NEW_LINE) {
			return 'T_NEW_LINE';
		}

		return token_name($token);
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

		while ($parenthesisCount > 0 && $pos < count($this->tokens)) {
			// Look for the next token
			$token = $this->peekTokenAt($pos);

			// Increment or decrement the parenthesis count
			if ($this->extractTokenText($token) == "(") {
				$parenthesisCount += 1;
			} else if ($this->extractTokenText($token) == ")") {
				$parenthesisCount -= 1;
			}

			// Increment the position
			$pos += 1;
		}

		return $pos;
	}




}
