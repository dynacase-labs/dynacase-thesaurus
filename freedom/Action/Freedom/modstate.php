<?php
/**
 * Document State modification
 *
 * @author Anakeen 2000 
 * @version $Id: modstate.php,v 1.7 2004/09/22 16:16:39 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */




include_once("FDL/Class.Doc.php");
include_once("FDL/modcard.php");



// -----------------------------------
function modstate(&$action) {
  // -----------------------------------
    
    
    
    // Get all the params      
  $docid=GetHttpVars("id");
  $state = GetHttpVars("newstate"); // new state
  $comment = GetHttpVars("comment"); // comment
  $force = (GetHttpVars("fstate","no")=="yes"); // force change

    
  if ( $docid == 0 ) $action->exitError(_("the document is not referenced: cannot apply state modification"));
    
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  
  
  // initialise object
    $doc = new Doc($dbaccess,$docid);
  
  


  
 
  if ($doc->wid > 0) {
    if ($state != "-") {
      $wdoc = new Doc($dbaccess,$doc->wid);
      $wdoc->Set($doc);
      setPostVars($wdoc);
      $err=$wdoc->ChangeState($state,$comment,$force);
      if ($err != "")  $action->AddWarningMsg($err);
    } else {
      if ($comment != "") $doc->addComment($comment);
    }
  } else {
    $action->AddLogMsg(sprintf(_("the document %s is not related to a workflow"),$doc->title));
  }
  
  
  
  
  
  
  
  
  
    redirect($action,GetHttpVars("redirect_app","FDL"),
	     GetHttpVars("redirect_act","FDL_CARD&refreshfld=Y&id=".$doc->id),
	     $action->GetParam("CORE_STANDURL"));
  
  
  
  
}




?>
