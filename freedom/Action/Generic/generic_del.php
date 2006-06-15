<?php
/**
 * Suppress a link to a folder
 *
 * @author Anakeen 2000 
 * @version $Id: generic_del.php,v 1.11 2006/06/15 15:59:11 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");
include_once("FDL/freedom_util.php");



/**
 * Put a doc in trash
 * @param Action &$action current action
 * @global id Http var : document id to trash
 * @global recursive Http var : if yes and it is a folder like family try to delete containt (primary relation) also
 */
function generic_del(&$action) {
// -----------------------------------


  // Get all the params      
  $docid=GetHttpVars("id");
  $recursive=(GetHttpVars("recursive")=="yes");
  $dbaccess = $action->GetParam("FREEDOM_DB");
   
  if ( $docid > 0 ) {



    $doc = new_Doc($dbaccess, $docid);
  
    // must unlocked before
    $err=$doc->CanLockFile();
    if ($err != "")  $action-> ExitError($err);

    // ------------------------------
    // delete document
    if ($recursive) {
       if ($doc->doctype=='D')   $err=$doc->deleteRecursive(); 
       else  $action->ExitError(sprintf(_("%s document it is not a folder and cannot support recursive deletion"),$doc->title));
    } else {
      $err=$doc->Delete();
    }
     if ($err != "")  $action-> ExitError($err);
     
     $action->AddActionDone("DELFILE",$doc->prelid);
     $action->AddActionDone("TRASHFILE",$doc->prelid);
     redirect($action,"FDL","FDL_CARD&id=$docid");
      
  }

  
  redirect($action,GetHttpVars("app"),"GENERIC_LOGO");

}
?>
