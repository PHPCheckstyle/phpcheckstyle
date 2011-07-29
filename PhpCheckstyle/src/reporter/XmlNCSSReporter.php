<?php
/*
 *  $Id: XmlFormatReporter.php 26740 2005-07-15 01:37:10Z hkodungallur $
 *
 *  Copyright(c) 2004-2005, SpikeSource Inc. All Rights Reserved.
 *  Licensed under the Open Source License version 2.1
 *  (See http://www.spikesource.com/license.html)
 */

require_once PHPCHECKSTYLE_HOME_DIR."/src/reporter/Reporter.php";

/**
 * Writes the count of lines of codes in an XML file.
 * Format compatible with javancss:
 * ================================
 * <?xml version="1.0"?>
 <javancss>
 <date>2009-09-01</date>
 <time>10:23:07</time>
 <packages>
 <package>
 <name>fr.ifn.calcul</name>
 <classes>3</classes>
 <functions>9</functions>
 <ncss>186</ncss>
 <javadocs>12</javadocs>
 <javadoc_lines>41</javadoc_lines>
 <single_comment_lines>24</single_comment_lines>
 <multi_comment_lines>0</multi_comment_lines>
 </package>
 * ================================
 *
 */
class XmlNCSSReporter {
	private $document = false;
	private $root = false;
	private $packages = false;
	private $objects = false;
	private $outputFile;
	private $lastPackageName = '';
	private $lastPackageChild = false;
	private $nbPackages = 0;
	private $packageClasses = 0;
	private $packageFunctions = 0;
	private $packageNCSS = 0;
	private $packageJavadocs = 0;
	private $packageJavadocLines = 0;
	private $packageSingleCommentLines = 0;
	private $packageMultiCommentLines = 0;

	/**
	 * Constructor; calls parent's constructor
	 *
	 * @param $ofile the output file name
	 */
	public function XmlNCSSReporter($ofile = false) {
		$this->outputFile = $ofile;
		if (!$this->outputFile) {
			$this->outputFile = "php://output";
		}
	}

	/**
	 * @see Reporter::start
	 * create the document root (<phpcheckstyle>)
	 *
	 */
	public function start() {
		$this->_initXml();
	}

	/**
	 * @see Reporter::start
	 * add the last element to the tree and save the DOM tree to the
	 * xml file
	 *
	 */
	public function stop() {

		$this->root->appendChild($this->objects);
		$this->document->save($this->outputFile);
	}

	/**
	 * @see Reporter::currentlyProcessing
	 * add the previous element to the tree and start a new elemtn
	 * for the new file
	 *
	 * @param $phpFile the file currently processed
	 * @SuppressWarnings checkUnusedFunctionParameters The parameter is inherited
	 */
	public function currentlyProcessing($phpFile) {
	}

	/**
	 * Write the count of lines for one file
	 *
	 * @param $fileName the file name
	 * @param $classes the number of classes in the file
	 * @param $functions the number of functions
	 * @param $ncss the number of non empty lines of code
	 * @param $javadocs the number of PHPDoc blocks
	 * @param $javadocLines the number of PHPDoc lines
	 * @param $singleCommentLines the number of single line comments
	 * @param $multiCommentLines the number of multi-line comments
	 */
	public function writeFileCount($fileName, $classes, $functions, $ncss, $javadocs, $javadocLines, $singleCommentLines, $multiCommentLines) {

		$fileName = str_replace('/', '.', $fileName);
		while (strpos($fileName, '.') == 0) {
			$fileName = substr($fileName, 1);
		}
		if (strlen($fileName) > 4) { // remove the .php at the end
			$fileName = substr($fileName, 0, -4);
		}

		// Identify the package name
		$packageName = substr($fileName, 0, strrpos($fileName, '.'));
		if ($this->lastPackageName == $packageName) {

			// Add the values
			$this->packageClasses = $this->packageClasses + $classes;
			$this->packageFunctions = $this->packageFunctions + $functions;
			$this->packageNCSS = $this->packageNCSS + $ncss;
			$this->packageJavadocs = $this->packageJavadocs + $javadocs;
			$this->packageJavadocLines = $this->packageJavadocLines + $javadocLines;
			$this->packageSingleCommentLines = $this->packageSingleCommentLines + $singleCommentLines;
			$this->packageMultiCommentLines = $this->packageMultiCommentLines + $multiCommentLines;

			// Remove the last node
			$this->packages->removeChild($this->lastPackageChild);

		} else {

			$this->nbPackages++;

			// Reset the values
			$this->packageClasses = $classes;
			$this->packageFunctions = $functions;
			$this->packageNCSS = $ncss;
			$this->packageJavadocs = $javadocs;
			$this->packageJavadocLines = $javadocLines;
			$this->packageSingleCommentLines = $singleCommentLines;
			$this->packageMultiCommentLines = $multiCommentLines;
		}

		//
		// Create the package bloc
		//
		$e = $this->document->createElement("package");

		$name = $this->document->createElement('name', $packageName);
		$e->appendChild($name);

		$classesE = $this->document->createElement('classes', $this->packageClasses);
		$e->appendChild($classesE);

		$functionsE = $this->document->createElement('functions', $this->packageFunctions);
		$e->appendChild($functionsE);

		$ncssE = $this->document->createElement('ncss', $this->packageNCSS);
		$e->appendChild($ncssE);

		$javadocsE = $this->document->createElement('javadocs', $this->packageJavadocs);
		$e->appendChild($javadocsE);

		$javadocLinesE = $this->document->createElement('javadoc_lines', $this->packageJavadocLines);
		$e->appendChild($javadocLinesE);

		$singleCommentLinesE = $this->document->createElement('single_comment_lines', $this->packageSingleCommentLines);
		$e->appendChild($singleCommentLinesE);

		$multiCommentLinesE = $this->document->createElement('multi_comment_lines', $this->packageMultiCommentLines);
		$e->appendChild($multiCommentLinesE);

		$this->packages->appendChild($e);
		$this->lastPackageChild = $e;

		//
		// Create the object bloc
		//
		$obj = $this->document->createElement("object");

		$objname = $this->document->createElement('name', $fileName);
		$obj->appendChild($objname);

		$objncss = $this->document->createElement('ncss', $ncss);
		$obj->appendChild($objncss);

		$objfunctions = $this->document->createElement('functions', $functions);
		$obj->appendChild($objfunctions);

		$objclasses = $this->document->createElement('classes', $classes);
		$obj->appendChild($objclasses);

		$objjavadocs = $this->document->createElement('javadocs', $javadocs);
		$obj->appendChild($objjavadocs);

		$this->objects->appendChild($obj);

		$this->lastPackageName = $packageName;
	}

	/**
	 * Write the total count of lines for the project.
	 *
	 * @param $nbFiles the number of files.
	 * @param $classes the number of classes
	 * @param $functions the number of functions
	 * @param $ncss the number of non empty lines of code
	 * @param $javadocs the number of PHPDoc blocks
	 * @param $javadocLines the number of PHPDoc lines
	 * @param $singleCommentLines the number of single line comments
	 * @param $multiCommentLines the number of multi-line comments
	 */
	public function writeTotalCount($nbFiles, $classes, $functions, $ncss, $javadocs, $javadocLines, $singleCommentLines, $multiCommentLines) {

		//
		// Create the total bloc
		//
		$e = $this->document->createElement("total");

		$classesE = $this->document->createElement('classes', $classes);
		$e->appendChild($classesE);

		$functionsE = $this->document->createElement('functions', $functions);
		$e->appendChild($functionsE);

		$ncssE = $this->document->createElement('ncss', $ncss);
		$e->appendChild($ncssE);

		$javadocsE = $this->document->createElement('javadocs', $javadocs);
		$e->appendChild($javadocsE);

		$javadocLinesE = $this->document->createElement('javadoc_lines', $javadocLines);
		$e->appendChild($javadocLinesE);

		$singleCommentLinesE = $this->document->createElement('single_comment_lines', $singleCommentLines);
		$e->appendChild($singleCommentLinesE);

		$multiCommentLinesE = $this->document->createElement('multi_comment_lines', $multiCommentLines);
		$e->appendChild($multiCommentLinesE);

		$this->packages->appendChild($e);

		//
		// Create the table bloc
		//
		$table = $this->document->createElement("table");
		$this->packages->appendChild($table);

		$header = $this->_createDOMLine("Packages", "Classes", "Functions", "NCSS", "Javadocs", "per");
		$table->appendChild($header);

		$projectLine = $this->_createDOMLine($this->nbPackages, $classes, $functions, $ncss, $javadocs, "Project");
		$table->appendChild($projectLine);

		if ($this->nbPackages == 0) {
			$packageLine = $this->_createDOMLine("", "", "", "", "", "Package");
		} else {
			$packageLine = $this->_createDOMLine("", $classes / $this->nbPackages, $functions / $this->nbPackages, $ncss / $this->nbPackages, $javadocs / $this->nbPackages, "Package");
		}
		$table->appendChild($packageLine);

		if ($classes == 0) {
			$packageLine = $this->_createDOMLine("", "", "", "", "", "Class");
		} else {
			$packageLine = $this->_createDOMLine("", "", $functions / $classes, $ncss / $classes, $javadocs / $classes, "Class");
		}
		$table->appendChild($packageLine);

		if ($functions == 0) {
			$packageLine = $this->_createDOMLine("", "", "", "", "", "Function");
		} else {
			$packageLine = $this->_createDOMLine("", "", "", $ncss / $functions, $javadocs / $functions, "Function");
		}
		$table->appendChild($packageLine);

		//
		// Create the averages bloc
		//
		$averages = $this->document->createElement("averages");

		$avgNcss = $this->document->createElement('ncss', $ncss / $nbFiles);
		$averages->appendChild($avgNcss);

		$avgNcss = $this->document->createElement('functions', $functions / $nbFiles);
		$averages->appendChild($avgNcss);

		$avgNcss = $this->document->createElement('classes', $classes / $nbFiles);
		$averages->appendChild($avgNcss);

		$avgNcss = $this->document->createElement('javadoc', $javadocs / $nbFiles);
		$averages->appendChild($avgNcss);

		$this->objects->appendChild($averages);

		//
		// Complete the objects bloc
		//
		$ncssobj = $this->document->createElement('ncss', $ncss);
		$this->objects->appendChild($ncssobj);
	}

	/**
	 * Create the DOM for a line of a table
	 */
	private function _createDOMLine($a, $b, $c, $d, $e, $f) {

		$line = $this->document->createElement("tr");
		$cola = $this->document->createElement("td", $a);
		$colb = $this->document->createElement("td", $b);
		$colc = $this->document->createElement("td", $c);
		$cold = $this->document->createElement("td", $d);
		$cole = $this->document->createElement("td", $e);
		$colf = $this->document->createElement("td", $f);

		$line->appendChild($cola);
		$line->appendChild($colb);
		$line->appendChild($colc);
		$line->appendChild($cold);
		$line->appendChild($cole);
		$line->appendChild($colf);

		return $line;

	}

	private function _initXml() {
		$this->document = new DomDocument("1.0");
		$this->root = $this->document->createElement('javancss');
		$this->document->appendChild($this->root);

		$date = $this->document->createElement('date', date("Y-m-d"));
		$this->root->appendChild($date);

		$time = $this->document->createElement('time', date("G:i:s"));
		$this->root->appendChild($time);

		$this->packages = $this->document->createElement('packages');
		$this->root->appendChild($this->packages);

		$this->objects = $this->document->createElement('objects'); // added later

	}

}
