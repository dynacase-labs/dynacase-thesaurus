<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: impcard.php,v 1.4 2004/10/19 16:05:41 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Doc.php");


// -----------------------------------
// -----------------------------------
function impcard(&$action) {
  // -----------------------------------

  // GetAllParameters

  $mime = GetHttpVars("mime"); // send to be view by word editor
  $ext = GetHttpVars("ext","html"); // extension
  $docid = GetHttpVars("id");
  $zonebodycard = GetHttpVars("zone"); // define view action
  $szone=false;

  $dbaccess = $action->GetParam("FREEDOM_DB");


  $doc = new Doc($dbaccess, $docid);
  $action->lay->set("TITLE",$doc->title);  
  if ($zonebodycard == "") $zonebodycard=$doc->defaultview;
  if ($zonebodycard == "") $zonebodycard="FDL:VIEWCARD";

  if (ereg("[A-Z]+:[^:]+:S", $zonebodycard, $reg))  $szone=true;// the zonebodycard is a standalone zone ?

  if ($szone) {
    // change layout
    include_once("FDL/viewscard.php");
    $action->lay = new Layout(getLayoutFile("FDL","viewscard.xml"),$action);
    viewscard(&$action); 
    
  }

  if ($mime != "") {
    $export_file = uniqid("/tmp/export").".$ext";
  
    $of = fopen($export_file,"w+");
    fwrite($of, $action->lay->gen());
    fclose($of);
  
    http_DownloadFile($export_file, chop($doc->title).".$ext", "$mime");
  
    unlink($export_file);
    exit;
  }
}


?>
