<?php

/**
 * FDL Forum edition action
 *
 * @author Anakeen 2000 
 * @version $Id: fdl_forumaddentry.php,v 1.2 2007/10/11 17:56:39 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
  http://127.0.0.7/freedom/?sole=Y&app=FDL&action=FDL_FORUMADDENTRY&docid=1243&fid=-1&lid=-1&eid=-1&text=------------------&flag=#FF0000
 */
include_once("FDL/Class.Doc.php");
include_once("FDL/freedom_util.php");

function fdl_forumaddentry(&$action) {

  $docid  = GetHttpVars("docid", "");
  $forid  = GetHttpVars("fid", "");
  $linkid = GetHttpVars("lid", -1);
  $entrid = GetHttpVars("eid", -1);
  $flag  = GetHttpVars("flag", "");
  $text   = GetHttpVars("text", "");

  $dbaccess = GetParam("FREEDOM_DB");


  if ($docid=="") $action->exitError(_("no document reference"));
  $doc = new_Doc($dbaccess, $docid);
  if (! $doc->isAffected()) $action->exitError(sprintf(_("cannot see unknow reference %s"),$docid));

  if ($doc->Control("edit")!="" && $doc->Control("forum")!="") 
    $action->exitError(sprintf(_("you don't have privilege to edit forum for document %s"),$doc->title));

  $doc->disableEditControl();

  if ($forid=="") $action->exitError(_("no forum document reference"));

  $date = $doc->getDate();

  if ($forid<=0) {

    $forum = createDoc($dbaccess, "FORUM");
    $forum->setValue("forum_docid", $doc->id);
    $forum->setProfil($doc->profid);
    $forum->Add();
    $doc->forumid = $forum->id;
    $doc->modify(true, array("forumid"));
    
    $t_id  = array($forum->getEntryId());
    $t_lid = array(-1); // Be sure they are no back reference in the first forum entry
    $t_userid = array($action->user->id);
    $t_user = array($doc->getTitle($dbaccess, $action->user->id));
    $t_text = array( $text );
    $t_flag = array( $flag );
    $t_date = array( $date );

  } else {

    $entrid = ($entrid == -1 ? $forum->getEntryId() : $entrid);
    $forum = new_Doc($dbaccess, $forid);
    if (! $forum->isAffected()) $action->exitError(sprintf(_("cannot see unknow forum reference %s"),$forid));

    $t_id     = $forum->getTValue("forum_d_id"); 
    $t_lid    = $forum->getTValue("forum_d_link");
    $t_userid = $forum->getTValue("forum_d_userid");
    $t_user   = $forum->getTValue("forum_d_user");
    $t_text   = $forum->getTValue("forum_d_text");
    $t_flag   = $forum->getTValue("forum_d_flag");
    $t_date   = $forum->getTValue("forum_d_date");

    $validlink = false;
    $ventry = -1;
    foreach ($t_id as $k => $v) {
      if ($linkid==$t_lid[$k] && $linkid!=-1) $validlink = true;
      if ($entrid==$v) $ventry = $k;
    }
    if ($validlink) $linkid=-1;
    if ($ventry==-1) $ventry = count($t_id);
    $t_id[$ventry] = $entrid; 
    $t_lid[$ventry]    = $linkid;
    $t_userid[$ventry] = $action->user->id;
    $t_user[$ventry]   = $doc->getTitle($dbaccess, $action->user->id);
    $t_text[$ventry]   = $text;
    $t_flag[$ventry]   = $flag;
    $t_date[$ventry]   = $date;
    
  }

  $forum->setValue("forum_d_id", $t_id);
  $forum->setValue("forum_d_link", $t_lid);
  $forum->setValue("forum_d_userid", $t_userid);
  $forum->setValue("forum_d_user", $t_user);
  $forum->setValue("forum_d_text", $t_text);
  $forum->setValue("forum_d_flag", $t_flag);
  $forum->setValue("forum_d_date", $t_date);
  $err =  $forum->Modify();
  if ($err!="") $action->exitError(sprintf(_("cannot modify forum %s"),$forum->id));;
  $err = $forum->postModify();
  if ($err!="") $action->exitError(sprintf(_("cannot modify forum %s"),$forum->id));;


  //  Redirect("FDL", "FDL????");


}


?>
