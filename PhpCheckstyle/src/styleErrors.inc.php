<?php
/*
 *  $Id: styleErrors.inc.php 26757 2005-07-15 03:04:26Z hkodungallur $
 *
 *  Copyright(c) 2004-2005, SpikeSource Inc. All Rights Reserved.
 *  Licensed under the Open Source License version 2.1
 *  (See http://www.spikesource.com/license.html)
 */

/**
 * Constants describing all the errors
 *
 * @author Hari Kodungallur <hkodungallur@spikesource.com>
 */

define("PHPCHECKSTYLE_INDENTATION_TAB", "The indentation line contains a tab");
define("PHPCHECKSTYLE_INDENTATION_WHITESPACE", "The indentation line contains a whitespace");

define("PHPCHECKSTYLE_INDENTATION_LEVEL", "Indentation problem, level shoud be %s but was %s");

define("PHPCHECKSTYLE_INDENTATION_LEVEL_MORE", "Indentation problem, level shoud be at least %s but was %s");

define("PHPCHECKSTYLE_WRONG_OPEN_TAG", "The php open tag must be '<?php'");

define("PHPCHECKSTYLE_NO_SPACE_BEFORE_TOKEN", "%s should not be preceded by whitespace");

define("PHPCHECKSTYLE_NO_SPACE_AFTER_TOKEN", "%s should not be followed by whitespace");

define("PHPCHECKSTYLE_SPACE_BEFORE_TOKEN", "Provide whitespace before %s");

define("PHPCHECKSTYLE_SPACE_AFTER_TOKEN", "Provide whitespace after %s");

define("PHPCHECKSTYLE_LEFT_CURLY_POS", "'{' should be on %s");

define("PHPCHECKSTYLE_CS_NO_OPEN_CURLY", "Control statement should always be placed within {} blocks");

define("PHPCHECKSTYLE_CS_STMT_ALIGNED_WITH_CURLY", "'%s' should be in the same line as '}'");

define("PHPCHECKSTYLE_END_BLOCK_NEW_LINE", "'}' should be on a new line");

define("PHPCHECKSTYLE_CONSTANT_NAMING", "Constant '%s' should be all uppercase (starting with a letter) with underscores to separate words");

define("PHPCHECKSTYLE_VARIABLE_NAMING", "Variable '%s' should start with a lowercase letter with no underscores to separate words");

define("PHPCHECKSTYLE_FUNCNAME_SPACE_AFTER", "Function call should not have a whitespace between function name and opening paranthesis");

define("PHPCHECKSTYLE_PRIVATE_FUNCNAME_NAMING", "Private function '%s' name should follow the pattern %s");

define("PHPCHECKSTYLE_PROTECTED_FUNCNAME_NAMING", "Protected function '%s' name should follow the pattern %s");

define("PHPCHECKSTYLE_FUNCNAME_NAMING", "Function '%s' name should follow the pattern %s");

define("PHPCHECKSTYLE_FUNC_DEFAULTVALUE_ORDER", "All arguments with default values should be at the end");

define("PHPCHECKSTYLE_CLASSNAME_NAMING", "Class name should start with an uppercase letter");

define("PHPCHECKSTYLE_NO_SHELL_COMMENTS", "Shell/Perl like comments (starting with '#') are not allowed");

define("PHPCHECKSTYLE_MISSING_DOCBLOCK", "Docblock missing");

define("PHPCHECKSTYLE_LONG_LINE", "Line contains more than %s characters");

define("PHPCHECKSTYLE_PROHIBITED_FUNCTION", "Calling function %s is prohibited");

define("PHPCHECKSTYLE_PROHIBITED_TOKEN", "Token %s is prohibited");

define("PHPCHECKSTYLE_CS_STMT_ON_NEW_LINE", "'%s' should be on the line after '}'");

define("PHPCHECKSTYLE_END_FILE_INLINE_HTML", "There is inline HTML at the end of the file");

define("PHPCHECKSTYLE_END_FILE_CLOSE_TAG", "There is a PHP close tag at the end of the file, this is not recommended");

define("PHPCHECKSTYLE_SILENCED_ERROR", "Errors should not be silenced when calling a function");

define("PHPCHECKSTYLE_VARIABLE_INSIDE_STRING", "Avoid encapsed variables inside a string");

define("PHPCHECKSTYLE_PASSING_REFERENCE", "Avoid passing parameters by reference");

define("PHPCHECKSTYLE_CYCLOMATIC_COMPLEXITY", "The cyclomatic complexity (%s) of this method is too high");

define("PHPCHECKSTYLE_TODO", "TODO %s");

define("PHPCHECKSTYLE_CONSTRUCTOR_NAMING", "The constructor naming should be %s");

define("PHPCHECKSTYLE_USE_BOOLEAN_OPERATORS", "Use boolean operators (&&) instead of logical operators (AND)");

define("PHPCHECKSTYLE_DOCBLOCK_RETURN", "The function returns a value but docblock @return is missing");

define("PHPCHECKSTYLE_DOCBLOCK_PARAM", "The function parameters does not match the docblock @param");

define("PHPCHECKSTYLE_DOCBLOCK_THROW", "The function throws an exception but the docblock @throw is missing");

define("PHPCHECKSTYLE_UNARY_OPERATOR", "Avoid using unary operators (++ or --) inside a control statement");

define("PHPCHECKSTYLE_INSIDE_ASSIGNMENT", "Avoid using assigments (=) inside a control statement");

define("PHPCHECKSTYLE_FUNCTION_LENGTH_THROW", "This function is too long (%s lines)");

define("PHPCHECKSTYLE_EMPTY_BLOCK", "Empty %s block");

define("PHPCHECKSTYLE_EMPTY_STATEMENT", "Empty statement (;;)");

define("PHPCHECKSTYLE_HEREDOC", "Heredoc syntax should be avoided");

define("PHPCHECKSTYLE_MAX_PARAMETERS", "This function has an excessive number of parameters (%s)");

define("PHPCHECKSTYLE_NEED_BRACES", "This block of code should have braces");

define("PHPCHECKSTYLE_SWITCH_DEFAULT", "Switch block need a default statement");

define("PHPCHECKSTYLE_SWITCH_DEFAULT_ORDER", "Switch default statement should be located after all cases");

define("PHPCHECKSTYLE_SWITCH_CASE_NEED_BREAK", "Case statement need a break");

define("PHPCHECKSTYLE_UNUSED_PRIVATE_FUNCTION", "Unused private function : %s");

define("PHPCHECKSTYLE_UNUSED_VARIABLE", "Variable %s is undeclared or not used");

define("PHPCHECKSTYLE_UNUSED_FUNCTION_PARAMETER", "Function parameter %s doesn't seem to be used");

define("PHPCHECKSTYLE_ONE_CLASS_PER_FILE", "Only one class declaration is allowed per file");

define("PHPCHECKSTYLE_FUNCTION_INSIDE_LOOP", "Consider moving the %s function outside the loop");

define("PHPCHECKSTYLE_UNUSED_CODE", "Unused code after return or throw");

define("PHPCHECKSTYLE_DEPRECATED_FUNCTION", "%s is deprecated since PHP %s, use %s instead");
