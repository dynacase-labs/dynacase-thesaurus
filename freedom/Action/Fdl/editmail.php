<?php
/**
 * Edition to send mail
 *
 * @author Anakeen 2000 
 * @version $Id: editmail.php,v 1.17 2006/07/27 16:20:18 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/Class.Doc.php");

// -----------------------------------
// -----------------------------------
/**
 * Edition to send mail
 * @param Action &$action current action
 * @global mid Http var : document id to send
 * @global mzone Http var : view zone to use to send mail
 * @global ulink Http var : with hyperlink (to use in internal) [Y|N]
 * @global dochead Http var : with header (icon/title) or not [Y|N]
 * @global viewdoc Http var : with preview of sended mail [Y|N]
 */
function editmail(&$action) {
  $docid = GetHttpVars("mid"); 
  $zone = GetHttpVars("mzone"); 
  $ulink = GetHttpVars("ulink"); 
  $dochead = GetHttpVars("dochead"); 
  $viewdoc = (GetHttpVars("viewdoc","Y")=="Y"); 

  $from = GetHttpVars("_mail_from","");
  $to = GetHttpVars("mail_to");
  $cc = GetHttpVars("mail_cc");

  // for compliance with old notation
  $ts=array();
  $tt=array();
  if ($to != "") {
    $ts=explode(",",$to);
    $tt=array_fill(0,count($ts),"to");
  }
  if ($cc != "") {
    $ts=array_merge($ts,explode(",",$cc));
    $tt=array_merge($tt,array_fill(0,count(explode(",",$cc)),"cc"));
  }
  setHttpVar("mail_recip",$ts);
  setHttpVar("mail_copymode",$tt);


  
  if ($from == "") {
    $from=getMailAddr($action->user->id, true);    
  }
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new_Doc($dbaccess, $docid);
  

  // control sending
  $err=$doc->control('send');
  if ($err != "") $action->exitError($err);

  if ($zone=="") $zone=$doc->defaultmview;

  $action->lay->Set("from",$from);
  $action->lay->Set("mid",$docid);
  $action->lay->Set("ulink",$ulink);
  $action->lay->Set("mzone",$zone);
  $action->lay->Set("dochead",$dochead);
  $action->lay->Set("title",$doc->title);
  $action->lay->set("VIEWDOC",$viewdoc);
  
}
?>