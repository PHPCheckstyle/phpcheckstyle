<?php 
define("PHPCHECKSTYLE_HOME_DIR", dirname(__FILE__));

require_once "vendor/autoload.php";

// default values
$options['format'] = "array"; // default format

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

// @TODO: Make this an includeable file
$configFile = array(
	'indentation' => array(
		"type" => "spaces",
		"number" => 2
	)
);

// Launch PHPCheckstyle
$style = new PHPCheckstyle\PHPCheckstyle($formats, $resultDir, $configFile, null, false, true);
$style->processFiles($sources, $options['exclude']);

echo "<pre>".print_r($style->_reporter->reporters[0]->outputFile, TRUE)."</pre>";

echo "Reporting Completed.</BR></BR>";

echo 'Display Results : <a href="'.$resultDir.'">'.$resultDir.'</a>';


?>