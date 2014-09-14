<?php
namespace PHPCheckstyle;

/**
 * Statement Item class.
 *
 * This object is stored in the "_branchingStack" array to keep track of nested statements.
 *
 * Inspired by http://www.phpcompiler.org/doc/latest/grammar.html
 *
 * @package classes
 * @SuppressWarnings checkUnusedVariables
 */
class StatementItem {
	// The statement type.
	// CLASS
	// FUNCTION
	// INTERFACE
	// IF
	// ELSE
	// ELSEIF
	// FOR
	// FOREACH
	// TRY
	// CATCH
	// DO
	// WHILE
	var $type = null;

	// The statement name
	var $name = null;

	// The begin line of the statement in the file
	var $line;

	// For FUNCTION statements
	var $visibility;

	// For SWITCH / CASE statements
	var $switchHasDefault = false; // indicate that the switch instruction has a case "default" set.
	var $caseHasBreak = false; // indicate that the current case has a break instruction
	var $caseStartLine = 0; // start line of the currently processed case

	// For DO / WHILE statements
	// indicate that we have met a DO statement (which will be described in another StatementItem, but it will be closed when we meet the WHILE).
	var $afterDoStatement = false;

	// For heredoc blocks
	var $inHeredoc = false; // used to desactivate the encapsedVariable rule inside a heredoc block

	// Flag indicating the the statement block is not sourrounded by {}
	var $noCurly = false;
}
