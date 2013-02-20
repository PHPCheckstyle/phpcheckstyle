#!/usr/bin/php
<?php
/**
 *  CLI file to run the PHPCheckstyle
 */
function usage() {
	echo "Usage: ".$_SERVER['argv'][0]." <options>\n";
	echo "\n";
	echo "    Options: \n";
	echo "       --src          Root of the source directory tree or a file.\n";
	echo "       --exclude      [Optional] A directory or file that needs to be excluded.\n";
	echo "       --format       [Optional] Output format (html/text/xml/xml_console/console/html_console). Defaults to 'html'.\n";
	echo "       --outdir       [Optional] Report Directory. Defaults to './style-report'.\n";
	echo "       --config       [Optional] The name of the config file'.\n";
	echo "       --debug        [Optional] Add some debug logs (warning, very verbose)'.\n";
	echo "       --linecount    [Optional] Generate a report on the number of lines of code (JavaNCSS format)'.\n";
	echo "       --progress  	  [Optional] Prints a message noting the file and every line that is covered by PHPCheckStyle.\n";
	echo "       --quiet  	    [Optional] Quiet mode.\n";
	echo "       --help         Display this usage information.\n";
	exit;
}

// default values
$options['src'] = false;
$options['exclude'] = array();
$options['format'] = "html"; // default format
$options['outdir'] = "./style-report"; // default ouput directory
$options['config'] = "default.cfg.xml";
$options['debug'] = false;
$options['progress'] = false;
$options['quiet'] = false;
$lineCountFile = null;

// loop through user input
for ($i = 1; $i < $_SERVER["argc"]; $i++) {
	switch ($_SERVER["argv"][$i]) {
		case "--src":
			$i++;
			$options['src'] = $_SERVER['argv'][$i];
			break;

		case "--outdir":
			$i++;
			$options['outdir'] = $_SERVER['argv'][$i];
			break;

		case "--exclude":
			$i++;
			$options['exclude'][] = $_SERVER['argv'][$i];
			break;

		case "--format":
			$i++;
			$options['format'] = $_SERVER['argv'][$i];
			break;

		case "--config":
			$i++;
			$options['config'] = $_SERVER['argv'][$i];
			break;

		case "--debug":
			$options['debug'] = true;
			break;
		case "--linecount":
			$options['linecount'] = true;
			break;

		case "--progress":
			$options['progress'] = true;
			break;

		case "--quiet":
			$options['quiet'] = true;
			break;

		case "--help":
			usage();
			break;
		default:
			usage();
		break;
	}
}

define("PHPCHECKSTYLE_HOME_DIR", dirname(__FILE__));
require_once PHPCHECKSTYLE_HOME_DIR."/src/PHPCheckstyle.php";

// check for valid format and set the output file name
// right now the output file name is not configurable, only
// the output directory is configurable (from command line)
$knownFormats = array('html', 'html_console', 'console', 'text', 'xml','xml_console');
$formats = explode(',', $options['format']);
$unknownFormats = array_diff($formats, $knownFormats);
if (!empty($unknownFormats)) {
	echo sprintf("\nUnknown format %s.\n\n", implode(', ', $unknownFormats));
	usage();
}

// check that source directory is specified and is valid
if ($options['src'] == false) {
	echo "\nPlease specify a source directory/file using --src option.\n\n";
	usage();
}

if (!empty($options['linecount'])) {
	$lineCountFile = "ncss.xml";
}

$style = new PHPCheckstyle($formats, $options['outdir'], $options['config'], $lineCountFile, $options['debug'], $options['progress']);
$style->processFiles($options['src'], $options['exclude']);

if (!$options['quiet']) {
	echo "\nReporting Completed.\n";
}

exit(0);
