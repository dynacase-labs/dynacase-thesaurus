<?php
/**
 * Edition to send mail
 *
 * @author Anakeen 2000 
 * @version $Id: editmail.php,v 1.10 2005/06/13 16:24:50 marc Exp $
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
  
  if ($from == "") {
    $from=getMailAddr($action->user->id, true);    
  }
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new Doc($dbaccess, $docid);
  

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