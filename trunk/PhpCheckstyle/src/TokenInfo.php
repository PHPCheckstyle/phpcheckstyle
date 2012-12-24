<?php
if (!defined("T_ML_COMMENT")) {
	define("T_ML_COMMENT", T_COMMENT);
}

define('T_NEW_LINE', 10000);
define('T_TAB', 10001);
define('T_SEMICOLON', 10002); // ;
define('T_BRACES_OPEN', 10003);  // { To avoid confusion with T_CURLY_OPEN
define('T_BRACES_CLOSE', 10004); // }
define('T_PARENTHESIS_OPEN', 10005); // (
define('T_PARENTHESIS_CLOSE', 10006); // )
define('T_COMMA', 10007); // ,
define('T_EQUAL', 10008); // =
define('T_CONCAT', 10009); // .
define('T_COLON', 10010); // :
define('T_MINUS', 10011); // -
define('T_PLUS', 10012); // +
define('T_IS_GREATER', 10013); // >
define('T_IS_SMALLER', 10014); // <
define('T_MULTIPLY', 10015); // *
define('T_DIVIDE', 10016); // /
define('T_QUESTION_MARK', 10017); // ?
define('T_MODULO', 10018); // %
define('T_EXCLAMATION_MARK', 10019); // !
define('T_AMPERSAND', 10020); // %
define('T_SQUARE_BRACKET_OPEN', 10021); // [
define('T_SQUARE_BRACKET_CLOSE', 10022); // ]
define('T_AROBAS', 10023); // @
define('T_UNKNOWN', -1);

/**
 * TokenInfo class.
 *
 * This object is returned by the tokenizer.
 *
 * @package classes
 * @SuppressWarnings checkUnusedVariables
*/
class TokenInfo {

	/**
	 * The token ID.
	 *
	 * @var Integer
	 */
	var	$id = null;

	/**
	 * The token text.
	 * @var String
	 */
	var	$text = null;

	/**
	 * The position of the token in the file.
	 *
	 * @var Integer
	 */
	var $position = null;

	/**
	 * The line number.
	 *
	 * @var Integer
	 */
	var $line;


	/**
	 * Return a string representation of the token.
	 * @return String
	 */
	public function toString() {
		$result .= "line : ".$this->line;
		$result .= ", pos : ".$this->position;
		$result .= ", id : ".$this->getName($this->id);

		// Rename some special chars
		$text = str_replace("\r\n", "\\r\\n", $this->text);
		$text = str_replace("\r", "\\r", $text);
		$text = str_replace("\n", "\\n", $text);

		$result .= ", text : ".$text;
		return $result;
	}

	/**
	 * Return the name of a token, including the NEW_LINE one.
	 *
	 * @return String the name of the token
	 */
	public function getName() {
		switch ($this->id) {
			case T_NEW_LINE:
				$result = 'T_NEW_LINE';
				break;
			case T_TAB:
				$result = 'T_TAB';
				break;
			case T_SEMICOLON:
				$result = 'T_SEMICOLON';
				break;
			case T_BRACES_OPEN:
				$result = 'T_BRACES_OPEN';
				break;
			case T_BRACES_CLOSE:
				$result = 'T_BRACES_CLOSE';
				break;
			case T_PARENTHESIS_OPEN:
				$result = 'T_PARENTHESIS_OPEN';
				break;
			case T_PARENTHESIS_CLOSE:
				$result = 'T_PARENTHESIS_CLOSE';
				break;
			case T_COMMA:
				$result = 'T_COMMA';
				break;
			case T_EQUAL:
				$result = 'T_EQUAL';
				break;
			case T_CONCAT:
				$result = 'T_CONCAT';
				break;
			case T_COLON:
				$result = 'T_COLON';
				break;
			case T_MINUS:
				$result = 'T_MINUS';
				break;
			case T_PLUS:
				$result = 'T_PLUS';
				break;
			case T_IS_GREATER:
				$result = 'T_IS_GREATER';
				break;
			case T_IS_SMALLER:
				$result = 'T_IS_SMALLER';
				break;
			case T_MULTIPLY:
				$result = 'T_MULTIPLY';
				break;
			case T_DIVIDE:
				$result = 'T_DIVIDE';
				break;
			case T_QUESTION_MARK:
				$result = 'T_QUESTION_MARK';
				break;
			case T_MODULO:
				$result = 'T_MODULO';
				break;
			case T_EXCLAMATION_MARK:
				$result = 'T_EXCLAMATION_MARK';
				break;
			case T_AMPERSAND:
				$result = 'T_AMPERSAND';
				break;
			case T_SQUARE_BRACKET_OPEN:
				$result = 'T_SQUARE_BRACKET_OPEN';
				break;
			case T_SQUARE_BRACKET_CLOSE:
				$result = 'T_SQUARE_BRACKET_CLOSE';
				break;
			case T_AROBAS:
				$result = 'T_AROBAS';
				break;
			case T_UNKNOWN:
				$result = 'T_UNKNOWN';
				break;
			default:
				$result = token_name($this->id);
		}

		return $result;
	}

}