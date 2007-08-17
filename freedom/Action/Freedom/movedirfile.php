<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: movedirfile.php,v 1.11 2007/08/17 14:52:12 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */


include_once("FDL/Lib.Dir.php");
include_once("FDL/freedom_util.php");  



// -----------------------------------
function movedirfile(&$action) {
  // -----------------------------------



  // Get all the params      
  $todirid=GetHttpVars("todirid");
  $fromdirid=GetHttpVars("fromdirid");
  $docid=GetHttpVars("docid");
  $return=GetHttpVars("return"); // return action may be folio


  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc = new_Doc($dbaccess, $docid);

  // add before suppress
  $dir= new_Doc($dbaccess, $todirid);
  $err = $dir->AddFile($docid);
  if ($err != "") $action->exitError($err);

  $action->AddLogMsg(sprintf(_("%s has been added in %s folder"),
			     $doc->title,
			     $dir->title));

  $dir2= new_Doc($dbaccess, $fromdirid);
  if (method_exists($dir2,"DelFile")) {
    $err = $dir2->DelFile($docid);
    if ($err != "") $action->exitError($err);
  
    $action->AddLogMsg(sprintf(_("%s has been removed in %s folder"),
			       $doc->title,
			       $dir2->title));
  }
  if (($doc->prelid==0) && ($err=="")) { // because deletion id done after add
    $doc->prelid=$dir->initid;
    $doc->modify(true,array("prelid"),true);    
  }

  
  
  if ($return == "folio")  redirect($action,GetHttpVars("app"),"FOLIOLIST&dirid=$todirid");
  else redirect($action,GetHttpVars("app"),"FREEDOM_VIEW&dirid=$todirid");
  
}




?>
