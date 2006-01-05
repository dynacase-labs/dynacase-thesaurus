<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Lib.WGCal.php,v 1.57 2006/01/05 16:35:53 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once("FDL/mailcard.php");
include_once("osync/Class.WSyncDate.php");
include_once("WGCAL/Lib.wTools.php");
include_once("EXTERNALS/WGCAL_external.php");

define("SEC_PER_DAY", 24*3600);
define("SEC_PER_HOUR", 3600);
define("SEC_PER_MIN", 60);

define(DATE_F_LONG, "%a %d %B %Y %H:%M:%S");
define(DATE_F_DAY, "%A %d %B %Y");
define(DATE_F_HM, "%H:%M");


// ---------------------------------------------------------------------------------------
// Left bar tools
// ---------------------------------------------------------------------------------------

define(CAL_T_NONE, -1);
define(CAL_T_NAVIGATOR, 0);
define(CAL_T_CALSELECTOR, 1);
define(CAL_T_TODAY, 2);

function WGCalToolSwitchState(&$action, $tool) {
  $val = "";
  $fnd = false;
  $all =  explode("|", $action->GetParam("WGCAL_U_TOOLSSTATE", ""));
  if (count($all)>0) {
    while (list($k, $v) = each($all)) {
      $t = explode("%",$v);
      if ($tool == $t[0]) {
	$ns = ($t[1] == 0 ? 1 : 0 );
	$fnd = true;
      }
      else 
	$ns = $t[1];
      $nv = $t[0]."%".$ns;
      if ($val!="") $val .= "|";
      $val .= $nv;
    }
  } 
  if (!$fnd) {
    if ($val!="") $val .= "|";
    $val = $tool."%1";
  }
  $action->parent->param->set("WGCAL_U_TOOLSSTATE", $val, PARAM_USER.$action->user->id, $action->parent->GetIdFromName("WGCAL"));
}

function WGCalToolIsVisible( &$action,  $tool ) {
  $state = false;
  $all =  explode("|", $action->GetParam("WGCAL_U_TOOLSSTATE", ""));
  if (count($all)>0) {
    while (list($k, $v) = each($all)) {
      $t = explode("%",$v);
      if ($t[0] == $tool) {
	$state = $t[1];
      }
    }
  }
  if ($state==1) $state = true;
  return $state;
}


function WGCalGetFirstDayOfMonth($ts) {
	if ($ts<=0) return false;
 	$mm = strftime("%m", $ts);
 	$yy = strftime("%Y", $ts);
	$fwdt = gmmktime ( 0, 0, 0, $mm, 1, $yy);
	return $fwdt;
}
 function WGCalGetFirstDayOfMonthN($ts) {
 	return strftime("%u", $ts);
}
      	
function WGCalGetAgendaEvents(&$action,$reid, $d1="",$d2="", $nofilter=false) {

  include_once('FDL/popup_util.php');

  $debug = false;


  $dbaccess = $action->GetParam("FREEDOM_DB");


  $dre=new_Doc($dbaccess,$reid);
  $edre = array();
  $edre=$dre->getEvents($d1,$d2);

  return $edre;
}
       	
function dbdate2ts($dbtime) {
  $sec = substr($dbtime,17,2);
  $min = substr($dbtime,14,2);
  $hou = substr($dbtime,11,2);
  $day = substr($dbtime,0,2);
  $mon = substr($dbtime,3,2);
  $yea = substr($dbtime,6,4);
  return gmmktime($hou, $min, $sec, $mon, $day, $yea);
}
function localFrenchDateToUnixTs($fdate) {
  if (preg_match("/^(\d\d)\/(\d\d)\/(\d\d\d\d)\s?(\d\d)?:?(\d\d)?:?(\d\d)?\s?(\w+)?$/", $fdate,$r)) {   
    $ds = mktime($r[4], $r[5], $r[6], $r[2], $r[1], $r[3]);
  } else {
    $ds = -1;
  }
  return $ds;
}



function WGCalGetRGroups(&$action, $uid) {
  $fug = array();
  $dbaccess = $action->GetParam("COREUSER_DB");
  $u = new User($dbaccess, $uid);
  $ug = $u->GetGroupsId();
  foreach ($ug as $k=>$v) {
    $gu = new User($dbaccess, $v);
    $fug[] = $gu->fid;
  }
  return $fug;
      
}

// Pour identifiant WHAT
// Class.User.php
//   /**
//    * get All ascendant group ids of the user object
//    */
//   function GetGroupsId()

// Par FREEDOM :
//  $u->getTValue("US_IDGROUP");


// Cela ne donne que les groupes directs. Il faut ensuite utiliser une autre fonctions pour avoir les pères de chacun des groupes d'appartenance.
// Class.Group.php /**
//    * get all parent (ascendant) group of this group
//    * @return array id
//    */
//   function getParentsGroupId($pgid="", $level=0) {


/*
 * $sendto = 0: mail is send to the rv owner
 *         = 1: mail is send to all attendees
 *         = 2: mail is send to all attendees except to the owner
 */
function sendRv(&$action, &$event, $sendto=0, $title, $reason="", $sendvcs=false) {
 
 if ($action->getParam("WGCAL_G_SENDMAILS", 0)==0) return;

 $to = $from = $cc = $bcc = "";

 $sendtoext = ($event->getValue("calev_attextmail", 0)==1 ? true : false);

 // Compute From:
 $fid = $event->getValue("CALEV_OWNERID");
 $uid = new_Doc($action->GetParam("FREEDOM_DB"), $fid);
 $from = addslashes($uid->getValue("TITLE"))." <".$uid->getValue("us_mail").">";
 if ($action->GetParam("WGCAL_U_RVMAILCC",0)==1) $bcc = $from;


 // Compute To: field
 $to = "";
 if ($sendto==1 || $sendto==2) {
   $attid = $event->getTValue("CALEV_ATTID", array()); 
   foreach ($attid as $k => $v) {
     if ($v != $uid->id) {
       $u = new_Doc($action->GetParam("FREEDOM_DB"), $v);
       if (($u->fromid!=128 && $sendtoext) || $u->fromid==128) {
	 $fullname = $u->getValue("TITLE");
	 $mail = $u->getValue("us_mail");
	 if ($mail!="") $to .= ($to==""?"":", ").addslashes($fullname)." <".$mail.">";
       }
     }
   }
 }
 if ($sendto==0 || $sendto==1) {
   $to .= ($to==""?"":", ").addslashes($uid->getValue("TITLE"))." <".getMailAddr($uid->getValue("US_WHATID")).">";
 }

 $afiles = array();
 if ($sendvcs) 
   $afiles = array(array($event->vcalendarview,"FreedomEvent-".$event->id."-".time().".vcs", "text/x-vcalendar" ));
		
 
 if ($to!="") {
    sendCard($action, 
	     $event->id, 
	     $to, 
	     $cc,
	     $title,
	     "WGCAL:MAILRV?ev=$event->id:S&msg=$reason",
	     true, 
	     "", 
	     $from, 
	     $bcc, 
	     "html", 
	     false,
	     $afiles );
//      AddWarningMsg("Mail: event(".$event->getValue("calev_evtitle").") From=[$from] To=[$to] Cc=[$cc] Bcc=[$bcc] Msg=[$reason]");
  }
}

function GetCalEvent($dbaccess, $ev, $cev) {
  
  if ($ev<1 && $cev<1) return false;
  if ($ev==-1) {
    $evid = $cev;
  } else {
    $evtmp = new_Doc($dbaccess, $ev);
    $evid = $evtmp->getValue("evt_idinitiator");
  }
  if ($evid<1) return false;
  $nev = new_Doc($dbaccess, $evid);
  return $nev;
}


function GroupExplode(&$action, $gid) {

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $groupfid = getIdFromName($dbaccess, "GROUP");
  $igroupfid = getIdFromName($dbaccess, "IGROUP");
  $g = new_Doc($dbaccess, $gid);
  if ($g->fromid!=$groupfid && $g->fromid!=$igroupfid) return array($gid);

  $udbaccess = $action->GetParam("COREUSER_DB");
  $ugrp = new User($udbaccess);
  $ulist = $ugrp->GetUsersGroupList($g->getValue("US_WHATID"));
  foreach ($ulist as $ku=>$vu)	$gr[] = $vu["fid"];
  return $gr;
}
 
function WGCalEvSetColor(&$action, &$event) {

  $dress = wGetRessDisplayed();

  // the current user is event owner ?
  $idcolor = ($action->user->fid == $event["evt_idcreator"] ? $event["evt_idcreator"] : -1);
  // the current user is an event ressource ?
  if ($idcolor == -1) {
    $tress = explode("|",$event["evt_idres"]);
    foreach ($tress as $kr => $vr) {
      if ($vr==$action->user->fid) $idcolor = $vr;
    }
  }
  // search for the first user in ressource
  if ($idcolor == -1) {
    while ((list($kdr, $vdr) = each($dress)) && $dcolor==-1) {
      while ((list($kr, $vr) = each($tress)) && $dcolor==-1) {
	if ($vr==$vdr->id) $idcolor = $vr;
      }
    }
  }

  if ($idcolor!=-1)  {
    foreach ($dress as $kr => $vr) {
      if ($idcolor == $vdr->id) return $vdr->color;
    }
  }
  return "lightgrey";  
}

function GetLastSyncDate($db) {
  global $action;
  $syncdate = new WSyncDate($db, $action->parent->user->fid);
  return ($syncdate->outlook_date);
}

?>
