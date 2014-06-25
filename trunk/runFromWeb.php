<?php 
define("PHPCHECKSTYLE_HOME_DIR", dirname(__FILE__));

require_once PHPCHECKSTYLE_HOME_DIR."/src/PHPCheckstyle.php";

// default values
$options['format'] = "html"; // default format

// Get user selection
$sourceDir = $_POST['sourceDir'];
$resultDir = $_POST['resultDir'];
$configFile = $_POST['configFile'];

if ($_POST['excludeFile']!="") {
	$expFile = explode(',', $_POST['excludeFile']);
	$options['exclude'] = $expFile;
}
else {
	$options['exclude'] = array();
}

//
$formats = explode(',', $options['format']);
$sources = explode(',', $sourceDir);

// Launch PHPCheckstyle
$style = new PHPCheckstyle($formats, $resultDir, $configFile, null, false, true);
$style->processFiles($sources, $options['exclude']);

echo "Reporting Completed.</BR></BR>";

echo 'Display Results : <a href="'.$resultDir.'">'.$resultDir.'</a>';


?>