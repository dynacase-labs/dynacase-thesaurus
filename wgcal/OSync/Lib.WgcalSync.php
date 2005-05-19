<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005 
 * @version $Id: Lib.WgcalSync.php,v 1.5 2005/05/19 16:01:22 marc Exp $
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

  $app = new Application();
  $app->Set("WGCAL",$CoreNull,$session);
  if ($app->user->login != $_SERVER['PHP_AUTH_USER']) {
    $session->Set("");
    $app->SetSession($session);
  }
  $action = new Action();
  $action->Set("",$app);
  return $action;
}
   

function WSyncGetDataDb() {
  global $action;
  $dbaccess = $action->getParam("FREEDOM_DB", "");
  if ($dbaccess=="") {
    $action->log->error("**ERR** Database specification error");
    exit;
  }
  return $dbaccess;
}

function WSyncGetAdminDb() {
  global $action;
  $dbaccess = $action->getParam("FREEDOM_DB", "");
  if ($dbaccess=="") {
    $action->log->error("**ERR** Database specification error");
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

function WSyncMSdate2Timestamp($date,$time, $tz=false) {
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

  if (!$tz) $timestamp = gmmktime( $h, $mi, $s, $mo, $d, $y );
  else $timestamp = mktime( $h, $mi, $s, $mo, $d, $y );
 return $timestamp;
}

function WSyncError($s) {
  global $action;
  print "<pre>WSyncError : $s</pre>";
  if (is_object($action)) $action->log->error($s);
}



function WSyncSend($debug=false, $text="", $s="", $cr=true) {
  if ($debug) syslog(LOG_INFO, "WSyncSend: $text=[$s]");
  print ($debug?"$text=":"").$s. ($cr?"\n":"");
}


function WSyncUpdateIds($eid=-1, $oid="") {
  global $action;
  $uid = $action->parent->user->fid;
  if ($eid<0) return false;
  $db = WSyncGetAdminDb($action);
  $evids = new WSyncIds($db, array($uid, $eid));
  if ($evids->IsAffected()) {
    $evids->outlook_id = $oid;
    $evids->Modify();
    $action->log->debug("Update oid for event($uid,$eid)");
  } else {
    $evids->user_id = $uid;
    $evids->event_id = $eid;
    $evids->outlook_id = $oid;
    $evids->Add();
    $action->log->debug("Add ids for event($uid,$eid,$oid)");
  }
  return true;
}
  
  
function WSyncInitEvent(&$dbdata,
			&$event, 
			$title = "(untitled)",
			$descr = "",
			$s_date = "",
			$s_time = "",
			$dur = 0,
			$access = "",
			$prio = "") {

  global $action;

  $debug = ($debug!=true?false:true); 
  
  $event->setValue("CALEV_OWNERID", $action->parent->user->fid);
  $u = new Doc($dbdata, $action->parent->user->fid);
  $event->setValue("CALEV_OWNER", $u->getTitle());
  $event->setValue("CALEV_ATTID", array($action->parent->user->fid));
  $event->setValue("CALEV_ATTTITLE", array($u->getTitle()));
  $event->setValue("CALEV_ATTGROUP", array(-1));
  $event->setValue("CALEV_EVTITLE", utf8_decode($title));
  $event->setValue("CALEV_EVNOTE", utf8_decode($descr));
  $event->setValue("CALEV_VISIBILITY", ($access=="P"?0:1));
  
  if ($s_date=="" || $s_time=="") return;

  $event->setValue("CALEV_START", $s_date." ".$s_time);
  if ($s_time == "00:00:00" && $dur == 1440) {
    $event->setValue("CALEV_END", $s_date." 23:59:59");
    $event->setValue("CALEV_TIMETYPE", 2);
  } else {
    $sfin = WSyncMSdate2Timestamp($s_date, $s_time) + ($dur * 60);
    $event->setValue("CALEV_END", date2db($sfin));
    $event->setValue("CALEV_TIMETYPE", 0);
  }

  $event->setValue("CALEV_EVCALENDARID", -1);
  $event->setValue("CALEV_EVCALENDAR", _("main calendar"));
  $event->setValue("CALEV_EVALARM", 0);

  $event->setValue("CALEV_REPEATMODE", 0);
  $event->setValue("CALEV_REPEATWEEKDAY", -1);
  $event->setValue("CALEV_REPEATMONTH", 0);
  $event->setValue("CALEV_REPEATUNTIL", 0);

  return;
}
			
?>