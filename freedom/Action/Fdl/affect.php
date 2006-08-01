<?php
/**
 * Functions to affect document to an user
 *
 * @author Anakeen 2000 
 * @version $Id: affect.php,v 1.2 2006/08/01 15:20:46 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("FDL/mailcard.php");

/**
 * Edition to affect document
 * @param Action &$action current action
 * @global id Http var : document id to affect
 * @global _id_affectuser Http var : user identificator to affect
 * @global _actioncomment Http var : description of the action
 */
function affect(&$action) {  
  $docid = GetHttpVars("id"); 
  $uid = GetHttpVars("_id_affectuser"); 
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc=new_doc($dbaccess,$docid);
  if (! $doc->isAlive()) $action->exitError(sprintf(_("document #%s not found. Affectation aborded"),$docid));
  $docu=new_doc($dbaccess,$uid);
  if (! $docu->isAlive()) $action->exitError(sprintf(_("user #%s not found. Affectation aborded"),$uid));
  
  $wuid=$docu->getValue("us_whatid");
  if (! ($wuid>0)) $action->exitError(sprintf(_("user #%s has not a real account. Affectation aborded"),$uid));
  $comment = GetHttpVars("_actioncomment"); 

  $err=$doc->canEdit();
  if ($err != "") $action->exitError(_("Affectation aborded")."\n".$err);
  
  $err=$doc->lock(false,$wuid);
  if ($err != "") $action->exitError(_("Affectation aborded")."\n".$err);
  if ($err == "") {
    $action->AddActionDone("LOCKFILE",$doc->id);
    $doc->delUTags("AFFECTED");
    $doc->addUTag($wuid,"AFFECTED",$comment);

    $action->addWarningMsg(sprintf(_("document %s has been affected to %s"),$doc->title,$docu->title));
    $doc->addComment(sprintf(_("Affected to %s"),$docu->title));

    $to=$docu->getValue("us_mail");
    $subject=sprintf(_("affectation for %s document"),$doc->title);
    $err=sendCard($action,$doc->id,$to,"",$subject,"",true,$comment,"","","htmlnotif");
    if ($err!="")   $action->addWarningMsg($err);
  }

  redirect($action,GetHttpVars("redirect_app","FDL"),
	   GetHttpVars("redirect_act","FDL_CARD&latest=Y&refreshfld=Y&id=".$doc->id),
	   $action->GetParam("CORE_STANDURL"));

}
?>