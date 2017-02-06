<?php
// This file is an exemple of PHP file containing bad comments.
// This file should generate 6 warnings with the default config.

# 1 - C style comment : noShellComments :: PHPCHECKSTYLE_NO_SHELL_COMMENTS

class Comments {  // 2 - Class docBlocks
	
	// 3 - docBlocks : No docblock :: PHPCHECKSTYLE_MISSING_DOCBLOCK
	function testComment($a) { // 4 - docBlocks : testParam ::  PHPCHECKSTYLE_DOCBLOCK_PARAM
		
		if ($a == null) {
			throw new exception('$a is null');  // 5 - docBlocks : testThrow :: PHPCHECKSTYLE_DOCBLOCK_THROW
		}
		
		return $a;  // 6 - docBlocks : testThrow :: PHPCHECKSTYLE_DOCBLOCK_RETURN
		
	}
	
}
