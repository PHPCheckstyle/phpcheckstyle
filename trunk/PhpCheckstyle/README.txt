
                      PHPCheckstyle
                      =============

1. Overview
------------

  PHPCheckstyle is an open-source tool that helps PHP programmers 
  adhere to certain coding conventions. The tools checks the input PHP 
  source code and reports any deviations from the coding convention.

  The tool uses the PEAR Coding Standards as the default coding convention. 
  But it allows you to configure it to suit your coding standards.

  Please visit http://code.google.com/p/phpcheckstyle/ for
  more information and documentation


2. Requirements
----------------

  * PHP 5.0 or newer. XSL extension needs to be enabled. It is enabled 
    by providing the --with-xsl=[libxslt-install-dir] to the configure line.
  * Web browser to view the checkstyle report (only for html view)
  * Tested to be working on Linux and Windows. 


3. Installation
----------------

  Just unzip the distribution.

    $> unzip PhpCheckstyle.zip

  This will create a directory called phpcheckstyle and expand all 
  files in it.


4. Usage
---------

    * Change directory to the PHPCheckstyle installation directory.

      $> cd phpcheckstyle

    * Execute the run.php script providing the --src option.

      $> php run.php --src <php source directory/file>

    * Use the --help option to see more options

      $> php run.php --help


