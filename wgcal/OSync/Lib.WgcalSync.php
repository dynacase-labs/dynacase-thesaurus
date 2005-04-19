<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005 
 * @version $Id: Lib.WgcalSync.php,v 1.3 2005/04/19 06:49:51 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */

include_once('WHAT/Class.Log.php');
include_once('WHAT/Lib.Http.php');
include_once('WHAT/Class.Session.php');
include_once('WHAT/Class.User.php');

function WSyncAuthent() {

  global $_GET;
  global $CORE_LOGLEVEL;
  $CoreNull = "";
  
  if (isset($_COOKIE['session'])) $sess_num= $_COOKIE['session'];
  else $sess_num=GetHttpVars("session");//$_GET["session"];

  $session=new Session();
  if (!  $session->Set($sess_num))  {
    print("<H4>Authentification failed</H4>");
    exit;
  };

  $core = new Application();
  $core->Set("CORE",$CoreNull,$session);
  if ($core->user->login != $_SERVER['PHP_AUTH_USER']) {
    $session->Set("");
    $core->SetSession($session);
  }
  return $core;
}
   

function WSyncGetDataDb(&$ctx) {
  $dbaccess = $ctx->getParam("FREEDOM_DB", "");
  if ($dbaccess=="") {
    $ctx->log->error("**ERR** Database specification error");
    exit;
  }
  return $dbaccess;
}

function WSyncGetAdminDb(&$ctx) {
  $dbaccess = $ctx->getParam("FREEDOM_DB", "");
  if ($dbaccess=="") {
    $ctx->log->error("**ERR** Database specification error");
    exit;
  }
  return $dbaccess;
}

function WSyncMSdate2Db( $day, $time=-1 )   {
  if (ereg("([0-9]{2})/([0-9]{2})/([0-9]{4})",$day, $regs))     {
    if(strlen($regs[1]) <2) $regs[1] = "0$regs[1]";
    if(strlen($regs[2]) <2) $regs[2] = "0$regs[2]";
    return "$regs[1]/$regs[2]/$regs[3]";
  } else {
    return false;
  }
}

function WSyncDbDate2Outlook($date, $withtime=true) {
  return substr($date,0, ($withtime?19:10));
}

function WSyncTs2Outlook($date) {
  return strftime("%d/%m/%Y %H:%M:%S", $date);
}

function WSyncMSdate2Timestamp($date,$time) {
  if (ereg("([0-9]{2})/([0-9]{2})/([0-9]{4})", $date, $regs)) {
    $y = $regs[3];
    $mo = $regs[2];
    $d = $regs[1];
  } else {
    return false;
  }
  if (ereg("([0-9]{1,2}):([0-9]{2}):([0-9]{2})", $time, $regs)) {
    $h = $regs[1];
    $mi = $regs[2];
    $s = $regs[3];
  } else {
    return false;
  }
  $timestamp = mktime( $h, $mi, $s, $mo, $d, $y );
  return $timestamp;
}

function WSyncError(&$c, $s) {
  print "<pre>WSyncError : $s</pre>";
  if (is_object($c)) $c->log->error($s);
}



function WSyncSend($debug=false, $s, $cr=true) {
  if ($debug) syslog(LOG_INFO, "WSyncSend: [$s]");
  print $s. ($cr?"\n":"");
}


function WSyncUpdateIds(&$ctx="", $uid=-1, $eid=-1, $oid=-1) {
  if ($uid<0 || $eid<0 || $oid<0 || $ctx=="") return false;
  $db = WSyncGetAdminDb($ctx);
  $evids = new WSyncIds($db, array($uid, $eid));
  if ($evids->IsAffected()) {
    $evids->outlook_id = $oid;
    $evids->Modify();
    $ctx->log->debug("Update oid for event($uid,$eid)");
  } else {
    $evids->user_id = $uid;
    $evids->event_id = $eid;
    $evids->outlook_id = $oid;
    $evids->Add();
    $ctx->log->debug("Add ids for event($uid,$eid,$oid)");
  }
  return true;
}
  
  

?>