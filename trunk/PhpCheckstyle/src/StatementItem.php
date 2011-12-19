<?php

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

	var	$type = null;  // The statement type (CLASS, FUNCTION, ...)

	var $name = null; // The statement name

	var $line; // The begin line of the statement in the file
	
	
	// For SWITCH / CASE statements
	var $switchHasDefault = false;  // indicate that the switch instruction has a case "default" set.
	var $caseHasBreak = false;  // indicate that the current case has a break instruction
	var $caseStartLine = 0; // start line of the currently processed case

}