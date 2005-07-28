<?php
/**
 * Document State modification
 *
 * @author Anakeen 2000 
 * @version $Id: modstate.php,v 1.9 2005/07/28 16:47:51 eric Exp $
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
    $doc = new_Doc($dbaccess,$docid);
  
  


  
 
  if ($doc->wid > 0) {
    if ($state != "-") {
      $wdoc = new_Doc($dbaccess,$doc->wid);
      $wdoc->Set($doc);
      setPostVars($wdoc);
      $err=$wdoc->ChangeState($state,$comment,$force);
      if ($err != "")  $action->AddWarningMsg($err);
      else $action->info(sprintf("Change state %s [%d] : %s",$doc->title,$doc->id,$state));
    } else {
      if ($comment != "") {
	$doc->addComment($comment); 
	$action->log->info(sprintf("Add comment %s [%d] : %s",$doc->title,$doc->id,$comment));
      }
    }
  } else {
    $action->AddLogMsg(sprintf(_("the document %s is not related to a workflow"),$doc->title));
  }
  
  
  
  
  
  
  
  
  
    redirect($action,GetHttpVars("redirect_app","FDL"),
	     GetHttpVars("redirect_act","FDL_CARD&refreshfld=Y&id=".$doc->id),
	     $action->GetParam("CORE_STANDURL"));
  
  
  
  
}




?>
