<?php
/**
 * insert the documents of $dirid in folder $id
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_insertfld.php,v 1.7 2005/04/05 17:29:38 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */


include_once("FDL/Lib.Dir.php");
include_once("FDL/freedom_util.php");  



// -----------------------------------
function freedom_insertfld(&$action) {
  // -----------------------------------

  // insert the documents of $dirid in folder $id
    //    PrintAllHttpVars();

  // Get all the params      
  $dirid=GetHttpVars("dirid");// source folder
  $docid=GetHttpVars("id");   // destination folder
  $mode=GetHttpVars("mode","latest");
  $clean=GetHttpVars("clean","N")=="Y"; // if want to clean source folder
  $folio=GetHttpVars("folio","N")=="Y"; // return in folio


  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc= new Doc($dbaccess, $docid);

  $err="";


  if (! method_exists($doc,"addfile")) $action->exitError(sprintf(_("the document %s is not a container"),
								  $doc->title));
  if ($dirid > 0) {
    $ldoc=getChildDoc($dbaccess,$dirid,0,"ALL",array(),1,"TABLE");
    $err=$doc->InsertMDoc($ldoc, $mode);
  
  }
  if ($err != "") $action->addWarningMsg($err);
  
  if ($clean) {
    $sfld = new Doc($dbaccess,$dirid );
    $sfld->Clear();
  }

  if ($folio) redirect($action,"FREEDOM","FOLIOLIST&dirid=".$doc->initid);
  else  redirect($action,"FREEDOM","FREEDOM_VIEW&dirid=".$doc->initid);
  
}




?>
