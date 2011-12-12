<?php
/**
 * This file is an exemple of PHP file containing bad comments.
  * This file should generate 5 warnings of indentation with the default config.
 */


# 1 - C style comment : noShellComments :: PHPCHECKSTYLE_NO_SHELL_COMMENTS


class comments {
	
	// 2 - docBlocks : No docblock :: PHPCHECKSTYLE_MISSING_DOCBLOCK
	function testComment($a) { // 3 - docBlocks : testParam ::  PHPCHECKSTYLE_DOCBLOCK_PARAM
		
		if ($a == null) {
			throw new exception('$a is null');  // 4 - docBlocks : testThrow :: PHPCHECKSTYLE_DOCBLOCK_THROW
		}
		
		return $a;  // 5 - docBlocks : testThrow :: PHPCHECKSTYLE_DOCBLOCK_RETURN
		
	}
	
}
