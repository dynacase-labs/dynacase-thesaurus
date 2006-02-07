<?php
/**
 * Edition to send mail
 *
 * @author Anakeen 2000 
 * @version $Id: editmail.php,v 1.16 2006/02/07 14:51:55 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/Class.Doc.php");

// -----------------------------------
// -----------------------------------
function editmail(&$action) {
  $docid = GetHttpVars("mid"); 
  $zone = GetHttpVars("mzone"); 
  $ulink = GetHttpVars("ulink"); 
  $dochead = GetHttpVars("dochead","Y"); 

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

  $action->lay->Set("from",$from);
  $action->lay->Set("mid",$docid);
  $action->lay->Set("ulink",$ulink);
  $action->lay->Set("mzone",$zone);
  $action->lay->Set("dochead",$dochead);
  $action->lay->Set("title",$doc->title);
  
}
?>