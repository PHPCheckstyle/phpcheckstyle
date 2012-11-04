<?php 
define("PHPCHECKSTYLE_HOME_DIR", dirname(__FILE__));

require_once PHPCHECKSTYLE_HOME_DIR."/src/PHPCheckstyle.php";
require_once PHPCHECKSTYLE_HOME_DIR."/src/util/Utility.php";

// default values
$options['exclude'] = array();
$options['format'] = "html"; // default format
$options['config'] = "default.cfg.xml";
$options['debug'] = false;
$options['progress'] = true;
$lineCountFile = null;

define("CONFIG_FILE", $options['config']);
define("DEBUG", $options['debug']);

// Get user selection
$sourceDir = $_POST['sourceDir'];
$resultDir = $_POST['resultDir'];

//
$formats = explode(',', $options['format']);

// Launch PHPCheckstyle
$style = new PHPCheckstyle($formats, $resultDir, $lineCountFile, $options['progress']);
$style->processFiles($sourceDir, $options['exclude']);

echo "Reporting Completed.</BR></BR>";

echo 'Display Results : <a href="'.$resultDir.'">'.$resultDir.'</a>';


?>