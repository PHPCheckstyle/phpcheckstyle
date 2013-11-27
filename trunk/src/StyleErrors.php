<?php
/**
 * Constants describing all the errors
 *
 * @author Hari Kodungallur <hkodungallur@spikesource.com>
 */

define("PHPCHECKSTYLE_INDENTATION_TAB", "Tab indentation must not be used.");

define("PHPCHECKSTYLE_INDENTATION_WHITESPACE", "Whitespace indentation must not be used.");

define("PHPCHECKSTYLE_INDENTATION_LEVEL", "The indentation level must be %s but was %s.");

define("PHPCHECKSTYLE_INDENTATION_LEVEL_MORE", "The indentation level must be at least %s but was %s.");

define("PHPCHECKSTYLE_WRONG_OPEN_TAG", "The php open tag must be '<?php'.");

define("PHPCHECKSTYLE_NO_SPACE_BEFORE_TOKEN", "Whitespace must not preceed %s.");

define("PHPCHECKSTYLE_NO_SPACE_AFTER_TOKEN", "Whitespace must not follow %s.");

define("PHPCHECKSTYLE_SPACE_BEFORE_TOKEN", "Whitespace must preceed %s.");

define("PHPCHECKSTYLE_SPACE_AFTER_TOKEN", "Whitespace must follow %s.");

define("PHPCHECKSTYLE_LEFT_CURLY_POS", "The block opening '{' must be on %s");

define("PHPCHECKSTYLE_CS_NO_OPEN_CURLY", "A {} block must enclose the control statement %s.");

define("PHPCHECKSTYLE_CS_STMT_ALIGNED_WITH_CURLY", "The block closure '}' must be on %s");

define("PHPCHECKSTYLE_END_BLOCK_NEW_LINE", "The block closure '}' must be on a new line.");

define("PHPCHECKSTYLE_CONSTANT_NAMING", "Constant %s name should follow the pattern %s.");

define("PHPCHECKSTYLE_VARIABLE_NAMING", "Variable %s name should follow the pattern %s.");

define("PHPCHECKSTYLE_LOCAL_VARIABLE_NAMING", "Local variable %s name should follow the pattern %s.");

define("PHPCHECKSTYLE_MEMBER_VARIABLE_NAMING", "Member variable %s name should follow the pattern %s.");

define("PHPCHECKSTYLE_TOPLEVEL_VARIABLE_NAMING", "Top level variable %s name should follow the pattern %s.");

define("PHPCHECKSTYLE_FUNCNAME_SPACE_AFTER", "Whitespace must not be between the function %s and the opening parenthesis '{'.");

define("PHPCHECKSTYLE_PRIVATE_FUNCNAME_NAMING", "Private function %s name should follow the pattern %s.");

define("PHPCHECKSTYLE_PROTECTED_FUNCNAME_NAMING", "Protected function %s name should follow the pattern %s.");

define("PHPCHECKSTYLE_FUNCNAME_NAMING", "Function %s name should follow the pattern %s.");

define("PHPCHECKSTYLE_FUNC_DEFAULTVALUE_ORDER", "All arguments with default values must be at the end of the block or statement.");

define("PHPCHECKSTYLE_TYPE_FILE_NAME_MISMATCH", "Type name %s must match file name %s.");

define("PHPCHECKSTYLE_CLASSNAME_NAMING", "Class %s name should follow the pattern %s.");

define("PHPCHECKSTYLE_INTERFACENAME_NAMING", "Interface %s name should follow the pattern %s.");

define("PHPCHECKSTYLE_FILENAME_NAMING", "File %s name should follow the pattern %s.");

define("PHPCHECKSTYLE_NO_SHELL_COMMENTS", "Shell/Perl like comments (starting with '#') must not be used.");

define("PHPCHECKSTYLE_MISSING_DOCBLOCK", "The %s %s must have a docblock comment.");

define("PHPCHECKSTYLE_LONG_LINE", "Line length (%s characters) must not exceed %s characters.");

define("PHPCHECKSTYLE_PROHIBITED_FUNCTION", "Function %s must not be called.");

define("PHPCHECKSTYLE_PROHIBITED_TOKEN", "Token %s must not be used.");

define("PHPCHECKSTYLE_CS_STMT_ON_NEW_LINE", "%s must be on the line after '}'");

define("PHPCHECKSTYLE_END_FILE_INLINE_HTML", "inline HTML must not be included at the end of the file.");

define("PHPCHECKSTYLE_END_FILE_CLOSE_TAG", "A php close tag must not be included at the end of the file.");

define("PHPCHECKSTYLE_SILENCED_ERROR", "Errors must not be silenced when calling a function");

define("PHPCHECKSTYLE_VARIABLE_INSIDE_STRING", "encapsed variables must not be used inside a string");

define("PHPCHECKSTYLE_PASSING_REFERENCE", "parameters must not be passed by reference");

define("PHPCHECKSTYLE_CYCLOMATIC_COMPLEXITY", "The cyclomatic complexity of function %s (%s) must not be higher than %s.");

define("PHPCHECKSTYLE_TODO", "TODO: %s.");

define("PHPCHECKSTYLE_GOTO", "The control statement 'goto' must not be used.");

define("PHPCHECKSTYLE_CONTINUE", "The control statement 'continue' must not be used.");

define("PHPCHECKSTYLE_CONSTRUCTOR_NAMING", "The constructor name must be %s.");

define("PHPCHECKSTYLE_USE_BOOLEAN_OPERATORS", "Boolean operators (&&) must be used instead of logical operators (AND).");

define("PHPCHECKSTYLE_DOCBLOCK_RETURN", "The function %s returns a value and must include @returns in its docblock.");

define("PHPCHECKSTYLE_DOCBLOCK_PARAM", "The function %s parameters must match those in its docblock @param.");

define("PHPCHECKSTYLE_DOCBLOCK_THROW", "The function %s throws an exception and must include @throws in its docblock.");

define("PHPCHECKSTYLE_UNARY_OPERATOR", "Unary operators (++ or --) must not be used inside a control statement");

define("PHPCHECKSTYLE_INSIDE_ASSIGNMENT", "Assigments (=) must not be used inside a control statement.");

define("PHPCHECKSTYLE_FUNCTION_LENGTH_THROW", "The function %s length (%s) must not exceed %s lines.");

define("PHPCHECKSTYLE_EMPTY_BLOCK", "Empty %s block");

define("PHPCHECKSTYLE_EMPTY_STATEMENT", "Empty statement (;;)");

define("PHPCHECKSTYLE_HEREDOC", "Heredoc syntax must not be used.");

define("PHPCHECKSTYLE_MAX_PARAMETERS", "The function %s 's number of parameters (%s) must not exceed %s.");

define("PHPCHECKSTYLE_NEED_BRACES", "The statement %s must contain its code within a {} block.");

define("PHPCHECKSTYLE_SWITCH_DEFAULT", "The switch statement must have a default case.");

define("PHPCHECKSTYLE_SWITCH_DEFAULT_ORDER", "The default case of a switch statement must be located after all other cases.");

define("PHPCHECKSTYLE_SWITCH_CASE_NEED_BREAK", "The case statement must contain a break.");

define("PHPCHECKSTYLE_UNUSED_PRIVATE_FUNCTION", "Unused private function : %s.");

define("PHPCHECKSTYLE_UNUSED_VARIABLE", "Undeclared or unused variable : %s.");

define("PHPCHECKSTYLE_UNUSED_FUNCTION_PARAMETER", "The function %s parameter %s is not used.");

define("PHPCHECKSTYLE_ONE_CLASS_PER_FILE", "File %s must not have multiple class declarations.");

define("PHPCHECKSTYLE_ONE_INTERFACE_PER_FILE", "File %s must not have multiple interface declarations.");

define("PHPCHECKSTYLE_FUNCTION_INSIDE_LOOP", "%s function must not be used inside a loop.");

define("PHPCHECKSTYLE_UNUSED_CODE", "function %s has unused code after %s.");

define("PHPCHECKSTYLE_DEPRECATED_FUNCTION", "%s is deprecated since PHP %s. %s must be used instead.");

define("PHPCHECKSTYLE_ALIASED_FUNCTION", "%s is an alias, consider replacing with %s.");

define("PHPCHECKSTYLE_REPLACED", "Consider replacing %s with %s.");

define("PHPCHECKSTYLE_USE_STRICT_COMPARE", "Please check is you should not use a strict comparison operator instead of %s.");

define("PHPCHECKSTYLE_EMPTY_FILE", "The file %s is empty.");
