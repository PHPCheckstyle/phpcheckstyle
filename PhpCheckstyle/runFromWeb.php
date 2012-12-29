<?php 
define("PHPCHECKSTYLE_HOME_DIR", dirname(__FILE__));

require_once PHPCHECKSTYLE_HOME_DIR."/src/PHPCheckstyle.php";

// default values
$options['exclude'] = array();
$options['format'] = "html"; // default format

// Get user selection
$sourceDir = $_POST['sourceDir'];
$resultDir = $_POST['resultDir'];
$configFile = $_POST['configFile'];
//
$formats = explode(',', $options['format']);

// Launch PHPCheckstyle
$style = new PHPCheckstyle($formats, $resultDir, $configFile, null, false, true);
$style->processFiles($sourceDir, $options['exclude']);

echo "Reporting Completed.</BR></BR>";

echo 'Display Results : <a href="'.$resultDir.'">'.$resultDir.'</a>';


?>