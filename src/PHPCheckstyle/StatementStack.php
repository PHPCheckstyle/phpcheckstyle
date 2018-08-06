<?php
namespace PHPCheckstyle;

/**
 * Statement stack class.
 * This object is used to store the current stack of nested statements.
 *
 * @package classes
 */
class StatementStack {

	/**
	 * Stack of items.
	 *
	 * @var Array[StatementItem]
	 */
	var $statements = array();


	var $defaultItem;

	/**
	 * Return the size of the stack.
	 *
	 * @return Integer
	 */
	function count() {
		return count($this->statements);
	}

	/**
	 * Add a statement to the stack.
	 *
	 * @param StatementItem $item
	 */
	function push($item) {
		array_push($this->statements, $item);
	}

	/**
	 * Get the top statement from the stack.
	 *
	 * @return StatementItem
	 */
	function pop() {
		return array_pop($this->statements);
	}

	/**
	 * Display the current branching stack.
	 *
	 * @return String
	 */
	function getStackDump() {
		$dump = "";
		$stackTypes = array(
			"FUNCTION",
			"INTERFACE",
			"CLASS"
		);
		foreach ($this->statements as $item) {
			$dump .= $item->type;
			if (in_array($item->type, $stackTypes)) {
				$dump .= "(" . $item->name . ")";
			}
			$dump .= " -> ";
		}

		return $dump;
	}

	/**
	 * Return the top stack item.
	 *
	 * @return StatementItem
	 */
	function getCurrentStackItem() {
		$topItem = end($this->statements);

		if (!empty($topItem)) {
			return $topItem;
		} else {
			// In case of a empty stack, we are at the root of a PHP file (with no class or function).
			// We return the default values
			return $this->getDefaultItem();
		}
	}

	/**
	 * Return the parent stack item.
	 *
	 * @return StatementItem
	 */
	function getParentStackItem() {
		if ($this->count() > 1) {
			return $this->statements[$this->count() - 2];
		} else {
			// In case of a empty stack, we are at the root of a PHP file (with no class or function).
			// We return the default values
			return $this->getDefaultItem();
		}
	}

	/**
	 * Return the parent function.
	 *
	 * @return StatementItem
	 */
	function getParentFunction() {
		for ($i = $this->count() - 1; $i >= 0; $i --) {
			$item = $this->statements[$i];
			if ($item->type === "FUNCTION") {
				return $item;
			}
		}
		// In case of a empty stack, we are at the root of a PHP file (with no class or function).
		// We return the default values
		return $this->getDefaultItem();
	}

	/**
	 * Return the parent class.
	 *
	 * @return StatementItem
	 */
	function getParentClass() {
		for ($i = $this->count() - 1; $i >= 0; $i --) {
			$item = $this->statements[$i];
			if ($item->type === "CLASS" || $item->type === "INTERFACE") {
				return $item;
			}
		}
		// In case of a empty stack, we are at the root of a PHP file (with no class or function).
		return $this->getDefaultItem();
	}

	/**
	 * return a Singleton of a StackItem corresponding to the file level.
	 */
	function getDefaultItem() {
		if ($this->defaultItem == null) {
			$this->defaultItem = new StatementItem();
		}
		return $this->defaultItem;
	}
}
