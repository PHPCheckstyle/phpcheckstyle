<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<xsl:transform 
     xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="checkstyle">

<xsl:variable name="files" select="//file"/>
<xsl:variable name="filesWithErrors" select="//file/error[1]"/>
<xsl:variable name="errors" select="//error"/>
<xsl:variable name="totalFiles" select="count($files)"/>
<xsl:variable name="totalErrorFiles" select="count($filesWithErrors)"/>
<xsl:variable name="totalErrors" select="count($errors)"/>

<html xmlns="http://www.w3.org/1999/xhtml">
  <head>

    <title>PHPCheckstyle Results</title>
        <link href="css/global.css" rel="stylesheet" type="text/css" />
  </head>
  
  
  <body>

        <div id="top" class="content-small" style="padding-left:10px;padding-top:3px;width:95%">
            <table border="0" cellpadding="0" cellspacing="0" >
                <tr>

                    <td width="10%">
                        <a href="http://code.google.com/p/phpcheckstyle/" style="text-decoration:none" target="_blank">
                            <img src="images/spikesource_logo.gif" border="0" style="padding-bottom:5px;"/>
                        </a>
                    </td>

                    <td style="text-align:right; vertical-align:bottom; padding-bottom:10px;">
                        <h1><font size="5">PHPCheckstyle</font></h1>
                    </td>

            </tr>

            <tr>
                    <td colspan="2" class="content-main" align="left" valign="top" style="border-top:3px solid #06c; padding-right:0px;">


<!-- Summary -->
                        <h1>Summary</h1>
                        <table class="spikeVerticalTable" cellpadding="4" cellspacing="0" width="100%" border="0">

                            <tr>
                                <td class="spikeVerticalTableHead" style="width:33%;">Number of Files Tested</td>
                                <td class="spikeVerticalTableCell" style="width:67%;"><span class="emphasis"><xsl:value-of select="$totalFiles"/></span></td>
                            </tr>

                            <tr>
                                <td class="spikeVerticalTableHead">Number of Files With Errors</td>
                                <td class="spikeVerticalTableCell"><span class="emphasis"><xsl:value-of select="$totalErrorFiles"/></span></td>

                            </tr>
                            <tr>
                                <td class="spikeVerticalTableHeadLast">Total Number of Errors</td>
                                <td class="spikeVerticalTableCellLast"><span class="emphasis"><xsl:value-of select="$totalErrors"/></span></td>

                            </tr>
                        </table>
                        <div class="ahrefTop"><a href="#top">Top</a></div>

<!-- Files With Errors -->
    <xsl:if test="$totalErrors != 0">
                        <h1>Files With Errors</h1>
                        <table width="100%" class="spikeDataTable" cellpadding="4" cellspacing="0">
                            <thead>

                                <tr>
                                    <th nowrap="nowrap" class="spikeDataTableHeadCenter" style="width:75%; test-align:left;">
                                        Filename
                                    </th>
                                    <th style="width:25%" class="spikeDataTableHeadCenterLast">
                                        Number of Errors
                                    </th>
                                </tr>
                          </thead>
                          <tbody>

        <xsl:for-each select="$files">
            <xsl:variable name="this" select="."/>
            <xsl:variable name="myname" select="@name"/>
            <xsl:variable name="fileTotal" select="count(//file[@name=$myname]/error)"/>
            <xsl:if test="$fileTotal != 0">

                                <tr>
                                    <td class="spikeDataTableCellLeftBorder">
                                        <a href="#{$myname}"><xsl:value-of select="$myname"/></a>

                                    </td>
                                    <td class="spikeDataTableCellLeft">
                                        <xsl:value-of select="$fileTotal"/>
                                    </td>
                                </tr>
            </xsl:if>
        </xsl:for-each>

                            </tbody>

                        </table>
                        <div class="ahrefTop"><a href="#top">Top</a></div>


<!-- Files with Errors Details -->
        <xsl:for-each select="$files">
            <xsl:variable name="myname" select="@name"/>
            <xsl:variable name="fileTotal" select="count(//file[@name=$myname]/error)"/>
            <xsl:if test="$fileTotal != 0">
                        <h1 id="{$myname}"><xsl:value-of select="$myname"/></h1>

                        <table width="100%" class="spikeDataTable" cellpadding="4" cellspacing="0">

                            <thead>
                                <tr>
                                    <th nowrap="nowrap" class="spikeDataTableHeadCenter" style="width:75%; test-align:left;">
                                        Error Message
                                    </th>
                                    <th style="width:25%" class="spikeDataTableHeadCenterLast">
                                        Line Number
                                    </th>                                    
                                     <th style="width:25%" class="spikeDataTableHeadCenterLast">
                                        Level
                                    </th>

                                </tr>
                          </thead>

                          <tbody>
            <xsl:for-each select="//file[@name=$myname]/error">
                                <tr>
                                    <td class="spikeDataTableCellLeftBorder">
                                        <xsl:value-of select="@message"/>
                                    </td>

                                    <td class="spikeDataTableCellLeftBorder">
                                        <xsl:value-of select="@line"/>
                                    </td>
                                    <td class="spikeDataTableCellLeft">
                                         <xsl:value-of select="@severity"/>
                                    </td>
                                </tr>
            </xsl:for-each>
                            </tbody>
                        </table>
                        <div class="ahrefTop"><a href="#top">Top</a></div>

            </xsl:if>

        </xsl:for-each>

    </xsl:if>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">

                        <div style="width:100%;">
                            <table cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td class="copyright" align="left">
                                        <a href="http://code.google.com/p/phpcheckstyle/">PHPCheckstyle Home</a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

  </body>

</html>
</xsl:template>
</xsl:transform>
