<?php
/*
 *  $Id: TokenUtils.php 28215 2005-07-28 02:53:05Z hkodungallur $
*
*  Copyright(c) 2004-2005, SpikeSource Inc. All Rights Reserved.
*  Licensed under the Open Source License version 2.1
*  (See http://www.spikesource.com/license.html)
*
*  Lexical Analysis.
*/

if (!defined("T_ML_COMMENT")) {
	define("T_ML_COMMENT", T_COMMENT);
}

define('T_NEW_LINE', -1);

/**
 * Class that stores the tokens for a particular class and provide
 * utility functions like getting the next/previous token,
 * checking whether the token is of particular type etc.
 *
 * @see http://www.php.net/manual/en/tokens.php
 * @author Hari Kodungallur <hkodungallur@spikesource.com>
 */
class TokenUtils {

	// Variables
	private $fileRef;
	private $tokens;
	private $totalNumTokens;
	private $curTokenNumber;

	/**
	 * Constructor
	 */
	public function TokenUtils() {
		$this->reset();
	}

	/**
	 * Tokenizes the input php file and stores all the tokens in the
	 * $this->tokens variable.
	 *
	 * @param String $filename the line where the token is found
	 * @return Integer the total nomber of tokens in the file
	 */
	public function tokenize($filename) {
		$contents = "";
		if (filesize($filename)) {
			$fp = fopen($filename, "r");
			$contents = fread($fp, filesize($filename));
			fclose($fp);
		}
		$this->tokens = $this->_getAllTokens($contents);
		$this->totalNumTokens = count($this->tokens);

		return $this->totalNumTokens;
	}

	/**
	 * Gets the next token.
	 *
	 * In the process moves the index to the next position.
	 *
	 * @return the token found
	 */
	public function getNextToken() {
		$ret = false;
		if ($this->curTokenNumber < $this->totalNumTokens) {
			$ret = $this->tokens[$this->curTokenNumber];
			$this->curTokenNumber++;
		}

		return $ret;
	}

	/**
	 * Peeks the token at a given position.
	 *
	 * @param Integer $position the position of the token
	 * @return the token found
	 */
	public function peekTokenAt($position) {
		if ($position < $this->totalNumTokens) {
			return $this->tokens[$position];
		} else {
			return "";
		}
	}

	/**
	 * Gives the current position in the tokenizer.
	 *
	 * @return current position of the Tokenizer
	 */
	public function getCurrentPosition() {
		return $this->curTokenNumber;
	}

	/**
	 * Peeks the next token, i.e., returns the next token without moving
	 * the index.
	 *
	 * @return true if the token is found (and update the line value)
	 */
	public function peekNextToken() {
		$ret = false;
		if ($this->curTokenNumber < $this->totalNumTokens) {
			$ret = $this->tokens[$this->curTokenNumber];
		}
		return $ret;
	}

	/**
	 * Peeks at the previous token. That is it returns the previous token
	 * without moving the index
	 *
	 * @return true if the token is found (and update the line value)
	 */
	public function peekPrvsToken() {
		$ret = false;
		if ($this->curTokenNumber > 1) {
			$ret = $this->tokens[$this->curTokenNumber - 2];
		}
		return $ret;
	}

	/**
	 * Peeks at the next valid token.
	 * A valid token is one that is neither a whitespace or a comment
	 *
	 * @param Integer $startPos the start position for the search
	 * @param Boolean $stopOnNewLine Indicate if we need to stop when we meet a new line
	 * @return TokenInfo the info about the token found
	 */
	public function peekNextValidToken($startPos = null, $stopOnNewLine = false) {
		$ret = false;
		$lineOffset = 0;
		$pos = $this->curTokenNumber; // defaut position for the search
		if ($startPos != null) {
			$pos = $startPos; // if defined, set the start position

		}
		while ($pos < $this->totalNumTokens) {
			$ret = $this->tokens[$pos];
			$pos++;
			if (is_array($ret)) {
				list($k, $v) = $ret;
				if ($k == T_WHITESPACE || $k == T_COMMENT || $k == T_ML_COMMENT || $k == T_DOC_COMMENT) {
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
		$tmpTokenNumber = $this->curTokenNumber - 2;
		$lineOffset = 0;
		while ($tmpTokenNumber > 0) {
			$ret = $this->tokens[$tmpTokenNumber];
			$tmpTokenNumber--;
			if (is_array($ret)) {
				list($k, $v) = $ret;
				if ($k == T_WHITESPACE || $k == T_COMMENT || $k == T_ML_COMMENT || $k == T_DOC_COMMENT) {
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
		$this->fileRef = false;
		$this->curTokenNumber = 0;
		$this->totalNumTokens = 0;
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
	public function checkProvidedToken($token, $value, $text = false) {
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
		$ret = false;
		$retInfo = $this->peekPrvsValidToken();
		$token = $retInfo->token;
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
	 * Check if a the next token exists (and if its value correspond to what is expected).
	 *
	 * @param Integer $value the token we're looking for
	 * @param String $text (optional) the text we're looking for
	 * @return true if the token is found
	 */
	public function checkNextToken($value, $text = false) {
		$ret = false;
		$token = $this->peekNextToken();
		if (is_array($token)) {
			// Case of a real token
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
	 * Find the next position of the string after the current position.
	 *
	 * @param String $text the text we're looking for
	 * @return Integer the position, -1 if not found
	 */
	public function findNextStringPosition($text) {

		$pos = $this->getCurrentPosition() + 1;

		while  ($pos < $this->totalNumTokens) {
			$token = $this->tokens[$pos];

			if ($text == $this->extractTokenText($token)) {
				return $pos;
			}

			$pos++;
		}

		return -1;
	}

	/**
	 * Check if the next token (including whitespaces) correspond to the text.
	 *
	 * @param String $text the text we're looking for
	 * @return true if the token is found
	 */
	public function checkNextTextToken($text) {
		$ret = false;
		$token = $this->peekNextToken();
		if (is_string($token)) {
			if ($token == $text) {
				$ret = true;
			}
		}
		return $ret;
	}

	/**
	 * Check if the next valid token (ignoring whitespaces) correspond to the text.
	 *
	 * @param String $text the text we're looking for
	 * @return true if the token is found
	 */
	public function checkNextValidTextToken($text) {
		$ret = false;
		$retInfo = $this->peekNextValidToken();
		if ($retInfo != null) {
			$token = $retInfo->token;
			if (is_string($token)) {
				if ($token == $text) {
					$ret = true;
				}
			}
		}
		return $ret;
	}

	/**
	 * Check if the next valid token (ignoring whitespaces) correspond to the specified token.
	 *
	 * @param Integer $value the value of the token we're looking for
	 * @param String $text the text we're looking for
	 * @return true if the token is found
	 */
	public function checkNextValidToken($value, $text = false) {
		$ret = false;
		$retInfo = $this->peekNextValidToken();
		$token = $retInfo->token;
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
	 * Check if the token correspond to a given text
	 *
	 * @param Integer $token the token
	 * @param String $text the text
	 * @return true if the token contains the text
	 */
	public function checkProvidedText($token, $text) {
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
			$splitData = preg_split('#(\r\n|\n)#', $tokenData, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

			foreach ($splitData as $data) {
				if ($data == "\r\n" || $data == "\n") {
					// This is a new line token
					$newTokens[] = array(T_NEW_LINE, $data);
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

}
