<?php
/**
 * Convert OpenDocument Spreadsheet to csv (semicolon)
 *
 * @author Anakeen 2000 
 * @version $Id: ods2csv.php,v 1.1 2006/08/15 13:56:10 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
define ("SEPCHAR", ';');

$inrow=false;
$nrow=0;
$ncol=0;
$rows=array();
$colrepeat=0;
function startElement($parser, $name, $attrs) {
  global $rows,$nrow,$inrow,$ncol,$colrepeat;
  if ($name ==  "TABLE:TABLE-ROW") {
    $inrow=true;
    $nrow++;
    $ncol=0;
    $rows[$nrow]=array_fill(0,16,"");
  }
  
  if ($name ==  "TABLE:TABLE-CELL") {

    if ($attrs["TABLE:NUMBER-COLUMNS-REPEATED"]) {
      $colrepeat=intval($attrs["TABLE:NUMBER-COLUMNS-REPEATED"]);      
     }
  }

}

function endElement($parser, $name) {
  global $rows,$nrow,$inrow,$ncol,$colrepeat;
  if ($name ==  "TABLE:TABLE-ROW") $inrow=false;


  if ($name ==  "TABLE:TABLE-CELL") {



    if (($colrepeat > 1) &&($colrepeat < 16)) {
      $rval=$rows[$nrow][$ncol];
      for ($i=1;$i<$colrepeat;$i++) {
	$ncol++;
	$rows[$nrow][$ncol]=$rval;
      }
      }
      //$ncol+=intval($attrs["TABLE:NUMBER-COLUMNS-REPEATED"]);
    
    $ncol++;
    $colrepeat=0;
  }

}

function characterData($parser, $data)
{
  global $rows,$nrow,$inrow,$ncol;
  if ($inrow) $rows[$nrow][$ncol].=str_replace(SEPCHAR,' - ',$data);
  //  print $data. "- ";
}


function xmlcontent2csv($xmlcontent,&$fcsv) {
  global $rows;
  $xml_parser = xml_parser_create();
  // Utilisons la gestion de casse, de manière à être sûrs de trouver la balise dans $map_array
  xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
  xml_parser_set_option($xml_parser, XML_OPTION_SKIP_WHITE, 0);
  xml_set_element_handler($xml_parser, "startElement", "endElement");
  xml_set_character_data_handler($xml_parser, "characterData");
 

 
    if (!xml_parse($xml_parser,$xmlcontent )) {
      return (sprintf("erreur XML : %s à la ligne %d",
		  xml_error_string(xml_get_error_code($xml_parser)),
		  xml_get_current_line_number($xml_parser)));
    }
  
  xml_parser_free($xml_parser);
  //print_r($rows);
  foreach ($rows as $k=>$row) {
    $fcsv.= implode(SEPCHAR,$row)."\n";
  }
}

function ods2content($odsfile,&$content) {
  if (! file_exists($odsfile)) return "file $odsfile not found";
  $cibledir=uniqid("/var/tmp/ods");
  
  $cmd = sprintf("unzip -j %s content.xml -d %s >/dev/null", $odsfile, $cibledir );
  system($cmd);
  
  $contentxml=$cibledir."/content.xml";
  if (file_exists($contentxml)) {
    $content=file_get_contents($contentxml);
    unlink($contentxml);
  }
  
  rmdir($cibledir);
}

$odsfile = GetHttpVars("odsfile"); // file ods (input)
$csvfile = GetHttpVars("csvfile"); // file xml (output)

if ($odsfile=="") {
  print "odsfile needed :usage  --odsfile=<ods file> [--csvfile=<csv file output>]\n";
  return;
 }

$err=ods2content($odsfile,$content);
if ($err == "") {
  $err=xmlcontent2csv($content,$csv);
  if ($err=="") {
    if ($csvfile) {
      $n=file_put_contents($csvfile,$csv);
      if ($n > 0) print sprintf(_("csv file <%s> wroted")."\n",$csvfile);
      else $err=sprintf(_("cannot write  %s"),$csvfile);
    } else print $csv;
  }
 }
if ($err != "") print "ERROR:$err\n";


?> 