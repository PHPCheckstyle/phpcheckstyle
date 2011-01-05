################################################################################
#  $Id: README 28218 2005-07-28 03:23:04Z hkodungallur $
#  
#  Copyright(c) 2004-2005, SpikeSource Inc. All Rights Reserved.
#  Licensed under the Open Source License version 2.1
#  (See http://www.spikesource.com/license.html)
################################################################################

                      Spike PHPCheckstyle
                      ===================

1. Overview
------------

  Spike PHPCheckstyle is an open-source tool that helps PHP programmers 
  adhere to certain coding conventions. The tools checks the input PHP 
  source code and reports any deviations from the coding convention.

  The tool uses the PEAR Coding Standards as the default coding convention. 
  But it allows you to configure it to suit your coding standards.

  Please visit http://www.spikesource.com/projects/phpcheckstyle/ for
  more information and documentation


2. Requirements
----------------

  * PHP 5.0 or newer. XSL extension needs to be enabled. It is enabled 
    by providing the --with-xsl=[libxslt-install-dir] to the configure line.
  * Web browser to view the checkstyle report (only for html view)
  * Tested to be working on Linux and Windows. 
    Please feel free to test it on other platforms and report any issues 
    at the Spike PHPCheckstyle forums: 
    http://www.spikesource.com/forums/viewforum.php?f=62


3. Installation
----------------

  Just unzip the distribution.

    $> unzip spikephpcheckstyle.zip

  This will create a directory called spikephpcheckstyle and expand all 
  files in it.


4. Usage
---------

    * Change directory to the Spike PHPCheckstyle installation directory.

      $> cd spikephpcheckstyle

    * Execute the run.php script providing the --src option.

      $> php run.php --src <php source directory/file>

    * Use the --help option to see more options

      $> php run.php --help


