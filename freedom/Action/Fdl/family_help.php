<?php
/**
 * Return Help Files
 *
 * @author Anakeen 2000 
 * @version $Id: family_help.php,v 1.3 2006/04/03 14:56:26 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 */
 /**
 */

include_once("Lib.Http.php");
include_once("FDL/Class.Doc.php");

function family_help(&$action) {
  
  $docid = GetHttpVars("id");

  $pdffile=getFamilyHelpFile($action,$docid);
  if ($pdffile) {
    $name = basename($pdffile);
    Http_DownloadFile($pdffile,"$name","application/pdf");
  } else {
    $errtext=sprintf( _("file for %s not found."),$name);
    $action->ExitError($errtext);
  }
}


function getFamilyHelpFile(&$action,$docid) {
  if (! is_numeric($docid))  $docid = getFamIdFromName($dbaccess,$docid);
  $dbaccess = $action->GetParam("FREEDOM_DB");
 
  $doc = new_Doc($dbaccess,$docid);
  if ($doc->isAlive()) {
    $name = $doc->name;
    if ($name != "") {
      $pdffile=$action->GetParam("CORE_PUBDIR")."/Docs/$name.pdf";
      if (file_exists($pdffile)) {
	return $pdffile;
      } 
    }
  
  } 
  return false;
}
?>