<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_editimport.php,v 1.6 2005/02/15 15:49:45 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */



// ---------------------------------------------------------------
include_once("FDL/import_file.php");
include_once("FDL/Lib.Dir.php");





// -----------------------------------
function freedom_editimport(&$action) {
  // -----------------------------------

  // Get all the params   
  $classid = GetHttpVars("classid",0); // doc familly
  $dirid = GetHttpVars("dirid",10); // directory to place imported doc (default unclassed folder)

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
 

  // build list of class document
  $query = new QueryDb($dbaccess,"Doc");
  $query->AddQuery("doctype='C'");

  $selectclass=array();

  $doc = new Doc($dbaccess, $classid);
  $tclassdoc = GetClassesDoc($dbaccess, $action->user->id,$classid,"TABLE");

  while (list($k,$cdoc)= each ($tclassdoc)) {
    $selectclass[$k]["idcdoc"]=$cdoc["initid"];
    $selectclass[$k]["classname"]=$cdoc["title"];
    if ($cdoc["initid"] == $classid) $selectclass[$k]["selected"]="selected";
    else $selectclass[$k]["selected"]="";
  }


  $action->lay->SetBlockData("SELECTCLASS", $selectclass);


  $lattr = $doc->GetImportAttributes();
  $format = "DOC;".(($doc->name!="")?$doc->name:$doc->id).";<special id>;<special dirid> ";

  $ttemp=explode(";",$format);
  while (list($k, $v) = each ($ttemp)) {
    $tformat[$k]["labeltext"]=htmlentities($v);    
  }

  while (list($k, $attr) = each ($lattr)) {
    $format .= "; ".$attr->labelText;
    $tformat[$k]["labeltext"]=$attr->labelText;
  }
  
  $action->lay->set("mailaddr",getMailAddr($action->user->id));

  $action->lay->SetBlockData("TFORMAT", $tformat);
  
  $action->lay->Set("cols",count($tformat));

  $action->lay->Set("dirid",$dirid);

  $action->lay->Set("format",$format);
}



?>
