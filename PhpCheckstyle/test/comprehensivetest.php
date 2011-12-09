<?


// no whitespace around operators (before and after) :: PHPCHECKSTYLE_NO_SPACE_BEFORE_TOKEN
// PHPCHECKSTYLE_NO_SPACE_AFTER_TOKEN
$_missingWhitespaceAroundOperators=5;

// no whitespace between params and { in function. :: PHPCHECKSTYLE_FUNCNAME_SPACE_AFTER





// arguments with default values at end of params :: PHPCHECKSTYLE_FUNC_DEFAULTVALUE_ORDER

// file name does not match the class name :: PHPCHECKSTYLE_TYPE_FILE_NAME_MISMATCH



/**
 * A badly named class, but everything else is accounted for.
 */
class badlynamedclass
{

// private function naming :: PHPCHECKSTYLE_PRIVATE_FUNCNAME_NAMING

	/**
	 * A badly named function, but everything else is accounted for.
	 *
	 * @param $_param parameter
	 * @param $_param2 parameter2
	 */
	private function badlyNamedfunc($_param, $_param2)
	{
	$this->x = $_param * $_param2;
	$this->_missingWhitespaceAroundOperators = $this->x;
	}
}

// ---------------------------------- TODO -------------------------


//tab indentation :: PHPCHECKSTYLE_INDENTATION_TAB

//whitespace indentation :: PHPCHECKSTYLE_INDENTATION_WHITESPACE
// incorrect indentation :: PHPCHECKSTYLE_INDENTATION_LEVEL
//not enough indentation :: PHPCHECKSTYLE_INDENTATION_LEVEL_MORE





// whitespace around parentheses (before and after) :: PHPCHECKSTYLE_SPACE_BEFORE_TOKEN, PHPCHECKSTYLE_SPACE_AFTER_TOKEN

// block opening on new line :: PHPCHECKSTYLE_LEFT_CURLY_POS

// control statement without block :: PHPCHECKSTYLE_CS_NO_OPEN_CURLY

// block closure alignment :: PHPCHECKSTYLE_CS_STMT_ALIGNED_WITH_CURLY

// block closure on new line ::  PHPCHECKSTYLE_END_BLOCK_NEW_LINE

//




?>