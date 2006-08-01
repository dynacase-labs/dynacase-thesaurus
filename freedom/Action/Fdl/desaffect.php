<?php
/**
 * Functions to un-affect document to an user
 *
 * @author Anakeen 2000 
 * @version $Id: desaffect.php,v 1.1 2006/08/01 15:20:46 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("FDL/mailcard.php");

/**
 * Edition to un-saffect document
 * @param Action &$action current action
 * @global id Http var : document id to affect
 * @global _id_affectuser Http var : user identificator to affect
 * @global _actioncomment Http var : description of the action
 */
function desaffect(&$action) {  
  $docid = GetHttpVars("id"); 
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc=new_doc($dbaccess,$docid);
  if (! $doc->isAlive()) $action->exitError(sprintf(_("document #%s not found. Unaffectation aborded"),$docid));


  $err=$doc->canEdit();
  if ($err != "") $action->exitError(_("Unaffectation aborded")."\n".$err);
  
  $err=$doc->unlock();
  if ($err != "") $action->exitError(_("Unaffectation aborded")."\n".$err);
  if ($err == "") {
    $action->AddActionDone("UNLOCKFILE",$doc->id);
    $doc->delUTags("AFFECTED");

    $action->addWarningMsg(sprintf(_("document %s has been unaffected"),$doc->title,$docu->title));
    $doc->addComment(sprintf(_("Unaffected"),$docu->title));

  }

  redirect($action,GetHttpVars("redirect_app","FDL"),
	   GetHttpVars("redirect_act","FDL_CARD&latest=Y&refreshfld=Y&id=".$doc->id),
	   $action->GetParam("CORE_STANDURL"));

}
?>