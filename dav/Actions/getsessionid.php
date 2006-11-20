<?php
/**
 * Get DAV session
 *
 * @author Anakeen 2006
 * @version $Id: getsessionid.php,v 1.2 2006/11/20 17:43:54 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage DAV
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("DAV/Class.FdlDav.php");


/**
 * Get DAV session id for current user
 * @param Action &$action current action
 * @param string $vid identificator for file <vaultid>-<docid>
 */
function getsessionid(&$action) {
  header('Content-type: text/xml; charset=utf-8'); 

  $mb=microtime();
  $vid = GetHttpVars("vid");
  $docid = GetHttpVars("docid");
  error_log("docid=$docid");

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $action->lay->set("warning","");

  $s=new HTTP_WebDAV_Server_Freedom();
  
  
  if ($err) $action->lay->set("warning",utf8_encode($err));
  
  $action->lay->set("CODE","OK");
  $sid=$s->getSession($docid,$vid,$action->user->login);
  if (!$sid) {
    $sid=md5(uniqid($vid));
    $s->addsession($sid,$vid,$docid,$action->user->login,time()+3600);
    if (!$s)  $action->lay->set("CODE","KO");
  }
  $sessid="$docid-$vid-$sid";
  $action->lay->set("sessid",$sessid);
  $action->lay->set("count",1);
  $action->lay->set("delay",microtime_diff(microtime(),$mb));					

}