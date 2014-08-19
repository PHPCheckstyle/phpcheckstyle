<?php 
namespace PHPCheckstyle\Config;

abstract class CheckStyleConfig {
	abstract public function parse();
	abstract public function getTestItems($test);
	abstract public function getConfigItems($config);
	abstract public function getTestExceptions($test);
	abstract public function getTest($test);
	abstract public function getTestLevel($test);
	abstract public function getTestRegExp($test);
	abstract public function getTestDeprecations($test);
	abstract public function getTestAliases($test);
	abstract public function getTestReplacements($test);
	abstract public function getTestProperty($test, $property);
}