<?php
/**
 * Suppress a link to a folder
 *
 * @author Anakeen 2000 
 * @version $Id: generic_del.php,v 1.10 2006/04/28 14:33:39 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");
include_once("FDL/freedom_util.php");

// -----------------------------------
function generic_del(&$action) {
// -----------------------------------


  // Get all the params      
  $docid=GetHttpVars("id");
  $dbaccess = $action->GetParam("FREEDOM_DB");
   
  if ( $docid > 0 ) {



    $doc = new_Doc($dbaccess, $docid);
  
    // must unlocked before
    $err=$doc->CanLockFile();
    if ($err != "")  $action-> ExitError($err);

    // ------------------------------
    // delete document
     $err=$doc-> Delete();
     if ($err != "")  $action-> ExitError($err);
     
     $action->AddActionDone("DELFILE",$doc->prelid);
     $action->AddActionDone("TRASHFILE",$doc->prelid);
     redirect($action,"FDL","FDL_CARD&id=$docid");
      
  }

  
  redirect($action,GetHttpVars("app"),"GENERIC_LOGO");

}
?>
