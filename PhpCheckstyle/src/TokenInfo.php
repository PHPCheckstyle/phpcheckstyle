<?php

/**
 * TokenInfo class.
 *
 * This object is returned by the tokenizer when we search for a token.
 *
 * @package classes
 * @SuppressWarnings checkUnusedVariables
 */
class TokenInfo {

	var	$token = null;  // The token ID

	var $position = null; // The position of the token in the file.

	var $lineOffset; // The offset of the line number.

}