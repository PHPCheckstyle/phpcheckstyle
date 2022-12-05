#!/usr/bin/php
<?php

/**
 * CLI file to run the PHPCheckstyle unit tests.
 */
require_once dirname(__FILE__) . "/../vendor/autoload.php";

class Runner
{

    function getInstance()
    {

        // default values
        $options['outdir'] = "./style-report"; // default ouput directory
        $options['config'] = dirname(__FILE__) . "/../config/default.cfg.xml";
        $options['debug'] = false;
        $options['progress'] = false;
        $options['lang'] = 'en-us';
        $options['quiet'] = false;
        $lineCountFile = null;

        // $formats = array('null');
        $formats = array(
            'console'
        );

        $phpcheckstyle = new PHPCheckstyle\PHPCheckstyle($formats, $options['outdir'], $options['config'], $lineCountFile, $options['debug'], $options['progress']);

        if (file_exists(__DIR__ . '/src/PHPCheckstyle/Lang/' . $options['lang'] . '.ini')) {
            $phpcheckstyle->setLang($options['lang']);
        }
        
        return $phpcheckstyle;
    }
}

$runner = new Runner();
$GLOBALS['runner'] = $runner;

