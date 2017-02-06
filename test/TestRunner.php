#!/usr/bin/php
<?php

/**
 * CLI file to run the PHPCheckstyle unit tests.
 */


// default values
$options['outdir'] = "./style-report"; // default ouput directory
$options['config'] =  dirname(__FILE__) . "/../config/default.cfg.xml";
$options['debug'] = false;
$options['progress'] = false;
$options['lang'] = 'en-us';
$options['quiet'] = false;
$lineCountFile = null;

$formats = array('null');

require_once PHPCHECKSTYLE_HOME_DIR . "/../vendor/autoload.php";

$style = new PHPCheckstyle\PHPCheckstyle($formats, $options['outdir'], $options['config'], $lineCountFile, $options['debug'], $options['progress']);

if (file_exists(__DIR__ . '/src/PHPCheckstyle/Lang/' . $options['lang'] . '.ini')) {
	$style->setLang($options['lang']);
}

$GLOBALS['PHPCheckstyle'] = $style;

