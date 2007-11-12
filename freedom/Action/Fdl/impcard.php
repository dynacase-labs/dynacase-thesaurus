<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: impcard.php,v 1.10 2007/11/12 16:30:39 eric Exp $
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
  $valopt=GetHttpVars("opt"); // value of  options
  $vid = GetHttpVars("vid"); // special controlled view
  $szone=false;

  $dbaccess = $action->GetParam("FREEDOM_DB");

  if ($valopt != "") {
    include_once("FDL/editoption.php");
    $doc=getdocoption($action);
    $docid=$doc->id;
  } else {
    $doc = new_Doc($dbaccess, $docid);
  }
  $action->lay->set("TITLE",$doc->title);  
  if (($zonebodycard=="") && ($vid != "")) {
    $cvdoc= new_Doc($dbaccess, $doc->cvid);
    $tview = $cvdoc->getView($vid);
    $zonebodycard=$tview["CV_ZVIEW"];
  }
  if ($zonebodycard == "") $zonebodycard=$doc->defaultview;
  if ($zonebodycard == "") $zonebodycard="FDL:VIEWCARD";


  $zo=$doc->getZoneOption($zonebodycard);
  if ($zo=="B") {
    // binary layout file
    $ulink=false;
    $target="ooo";
    $file=$doc->viewdoc($zonebodycard,$target,$ulink);
    Http_DownloadFile($file,$doc->title.".odt",'application/vnd.oasis.opendocument.text',false,false);
    @unlink($file);
    exit;
  }

  if ($zo=='S')  $szone=true;// the zonebodycard is a standalone zone ?
  $action->lay->set("nocss",($zo=="U"));
  if ($szone) {
    // change layout
    include_once("FDL/viewscard.php");
    $action->lay = new Layout(getLayoutFile("FDL","viewscard.xml"),$action);
    viewscard($action); 
    
  }

  if ($mime != "") {
    $export_file = uniqid("/tmp/export").".$ext";
  
    $of = fopen($export_file,"w+");
    fwrite($of, $action->lay->gen());
    fclose($of);
  
    http_DownloadFile($export_file, chop($doc->title).".$ext", "$mime",false,false);
  
    unlink($export_file);
    exit;
  }
}


?>
