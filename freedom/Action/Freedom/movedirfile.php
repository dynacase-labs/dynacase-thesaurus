<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: movedirfile.php,v 1.9 2004/06/11 16:13:19 eric Exp $
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

  $doc = new Doc($dbaccess, $docid);

  // add before suppress
  $dir= new Doc($dbaccess, $todirid);
  $err = $dir->AddFile($docid);
  if ($err != "") $action->exitError($err);

  $action->AddLogMsg(sprintf(_("%s has been added in %s folder"),
			     $doc->title,
			     $dir->title));

  $dir= new Doc($dbaccess, $fromdirid);
  if (method_exists($dir,"DelFile")) {
    $err = $dir->DelFile($docid);
    if ($err != "") $action->exitError($err);
  
    $action->AddLogMsg(sprintf(_("%s has been removed in %s folder"),
			       $doc->title,
			       $dir->title));
  }

  
  
  if ($return == "folio")  redirect($action,GetHttpVars("app"),"FOLIOLIST&dirid=$todirid");
  else redirect($action,GetHttpVars("app"),"FREEDOM_VIEW&dirid=$todirid");
  
}




?>
