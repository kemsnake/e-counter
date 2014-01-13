<?php

/**
 * Simple excel generating from PHP5
 *
 * @package Utilities
 * @license http://www.opensource.org/licenses/mit-license.php
 * @author Oliver Schwarz <oliver.schwarz@gmail.com>
 * @version 1.0
 */

/**
 * Generating excel documents on-the-fly from PHP5
 *
 * Uses the excel XML-specification to generate a native
 * XML document, readable/processable by excel.
 *
 * @package Utilities
 * @subpackage Excel
 * @author Oliver Schwarz <oliver.schwarz@vaicon.de>
 * @version 1.1
 *
 * @todo Issue #4: Internet Explorer 7 does not work well with the given header
 * @todo Add option to give out first line as header (bold text)
 * @todo Add option to give out last line as footer (bold text)
 * @todo Add option to write to file
 */
class Excel_XML {

  /**
   * Header (of document)
   * @var string
   */
  private $header = "<?xml version=\"1.0\" encoding=\"%s\"?\>\n<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:o=\"urn:schemas-microsoft-com:office:office\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"http://www.w3.org/TR/REC-html40\">";

  /**
   * Styles (of document)
   * @var string
   */
  private $styles = "<Styles>
    <Style ss:ID=\"Default\" ss:Name=\"Normal\">
      <Alignment ss:Vertical=\"Bottom\"/>
      <Borders/>
      <Font ss:FontName=\"Times New Roman\" ss:Size=\"10\"/>
      <Interior/>
      <NumberFormat/>
      <Protection/>
    </Style>

    <Style ss:ID=\"shortDate\">
      <Borders>
        <Border ss:Position=\"Top\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
        <Border ss:Position=\"Right\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
        <Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
        <Border ss:Position=\"Left\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
      </Borders>
      <NumberFormat ss:Format=\"Short Date\"/>
    </Style>

    <Style ss:ID=\"th\">
      <Alignment ss:Horizontal=\"Center\"/>
      <Borders>
        <Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
        <Border ss:Position=\"Left\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
        <Border ss:Position=\"Right\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
        <Border ss:Position=\"Top\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
      </Borders>
      <Font ss:FontName=\"Times New Roman\" ss:Size=\"10\" ss:Bold=\"1\"/>
    </Style>

    <Style ss:ID=\"td\">
      <Borders>
        <Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
        <Border ss:Position=\"Left\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
        <Border ss:Position=\"Right\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
        <Border ss:Position=\"Top\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
      </Borders>
    </Style>

    <Style ss:ID=\"caption\">
      <Alignment ss:Horizontal=\"Center\"/>
      <Font ss:FontName=\"Times New Roman\" ss:Size=\"20\"/>
    </Style>
  </Styles>";

  /**
   * Footer (of document)
   * @var string
   */
  private $footer = "</Workbook>";

  /**
   * Lines to output in the excel document
   * @var array
   */
  private $lines = array();

  /**
   * Used encoding
   * @var string
   */
  private $sEncoding;

  /**
   * Convert variable types
   * @var boolean
   */
  private $bConvertTypes;

  /**
   * Worksheet title
   * @var string
   */
  private $sWorksheetTitle;

  /**
   * Constructor
   *
   * The constructor allows the setting of some additional
   * parameters so that the library may be configured to
   * one's needs.
   *
   * On converting types:
   * When set to true, the library tries to identify the type of
   * the variable value and set the field specification for Excel
   * accordingly. Be careful with article numbers or postcodes
   * starting with a '0' (zero)!
   *
   * @param string $sEncoding Encoding to be used (defaults to UTF-8)
   * @param boolean $bConvertTypes Convert variables to field specification
   * @param string $sWorksheetTitle Title for the worksheet
   */
  public function __construct($sEncoding = 'UTF-8', $bConvertTypes = FALSE, $sWorksheetTitle = 'Table1') {
    $this->bConvertTypes = $bConvertTypes;
    $this->setEncoding($sEncoding);
    $this->setWorksheetTitle($sWorksheetTitle);
  }

  /**
   * Set encoding
   * @param string Encoding type to set
   */
  public function setEncoding($sEncoding) {
    $this->sEncoding = $sEncoding;
  }

  /**
   * Set worksheet title
   *
   * Strips out not allowed characters and trims the
   * title to a maximum length of 31.
   *
   * @param string $title Title for worksheet
   */
  public function setWorksheetTitle($title) {
    $title = preg_replace("/[\\\|:|\/|\?|\*|\[|\]]/", "", $title);
    $title = substr($title, 0, 31);
    $this->sWorksheetTitle = $title;
  }

  /**
   * Add row
   *
   * Adds a single row to the document. If set to true, self::bConvertTypes
   * checks the type of variable and returns the specific field settings
   * for the cell.
   *
   * @param array $array One-dimensional array with row content
   */
  private function addRow($array, $style_id = 'Default') {
    $cells = "";
    foreach ($array as $k => $v):
      $type = 'String';
      if ($this->bConvertTypes === TRUE && is_numeric($v)):
        $type = 'Number';
      endif;
      $v = htmlentities($v, ENT_COMPAT, $this->sEncoding);
      $cells .= "<Cell ss:StyleID=\"$style_id\"><Data ss:Type=\"$type\">" . $v . "</Data></Cell>\n";
    endforeach;
    $this->lines[] = "<Row>\n" . $cells . "</Row>\n";
  }

  /**
   * Add an array to the document
   * @param array 2-dimensional array
   */
  public function addArray($array, $style_id = 'Default') {
    foreach ($array as $k => $v) {
      $this->addRow($v, $style_id);
    }
  }

  /**
   * Add header to sheet
   * @param string header
   */
  public function addHeader($header) {
    foreach ($header as $row){
      $this->lines[] = $row;
    }
  }


  /**
   * Generate the excel file
   * @param string $filename Name of excel file to generate (...xls)
   */
  public function generateXML($filename = 'excel-export') {
    // correct/validate filename
    $filename = preg_replace('/[^aA-zZ0-9\_\-]/', '', $filename);

    // deliver header (as recommended in php manual)
    header("Content-Type: application/vnd.ms-excel; charset=" . $this->sEncoding);
    header("Content-Disposition: inline; filename=\"" . $filename . ".xls\"");

    // print out document to the browser
    // need to use stripslashes for the damn ">"
    echo stripslashes(sprintf($this->header, $this->sEncoding));
    echo "\n<Worksheet ss:Name=\"" . $this->sWorksheetTitle . "\">\n<Table>\n";
    foreach ($this->lines as $line) {
      echo $line;
    }

    echo "</Table>\n</Worksheet>\n";
    echo $this->footer;

  }

  /**
   * generateAndSaveXML
   *
   * Generate the XML and save to file
   *
   * @param unknown_type $filename
   */
  function generateAndSaveXML($filename) {

    //$content = stripslashes ($this->header);
    $content = stripslashes(sprintf($this->header, $this->sEncoding));
    // добавим стили
    $content .= $this->styles;
    $content .= "\n<Worksheet ss:Name=\"" . $this->sWorksheetTitle . "\">\n<Table>\n";
    $content .= "<Column ss:Index=\"1\" ss:AutoFitWidth=\"0\" ss:Width=\"110\"/>\n";
    $content .= implode("\n", $this->lines);
    $content .= "</Table>\n</Worksheet>\n";
    $content .= $this->footer;

    if (!$handle = @fopen($filename, 'w')) {
      return FALSE;
    }
    if (@fwrite($handle, $content) === FALSE) {
      return FALSE;
    }
    @fclose($handle);
    return TRUE;

  }

}

?>