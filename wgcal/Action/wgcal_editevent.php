<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_editevent.php,v 1.33 2005/03/09 22:27:44 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

include_once("FDL/Lib.Util.php");
include_once("FDL/Class.Doc.php");
include_once("WGCAL/WGCAL_external.php");


function wgcal_editevent(&$action) {

  $db = $action->getParam("FREEDOM_DB");

  $fq = getIdFromName($dbaccess, "WG_DISPONIBILITY");
  $action->lay->set("planid", $fq);

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/DHTMLapi.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/PopupWindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/ColorPicker2.js");

  $action->parent->AddJsRef("FDL/Layout/jdate.js");

  $action->parent->AddJsRef("jscalendar/Layout/calendar.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-setup.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-fr.js");

  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_editevent.js");
  
  $cssfile = $action->GetLayoutFile("calendar-default.css");
  $csslay = new Layout($cssfile,$action);
  $action->parent->AddCssCode($csslay->gen());
  

  $nh = GetHttpVars("nh", 0);
  $times = GetHttpVars("ts", time());
  $timee = GetHttpVars("te", time()+3600);
  // This is the event id NOT THE RV id
  $ev = GetHttpVars("ev", -1);
  
  if ($ev==-1) $evid = -1;
  else {
    $evtmp = new Doc($db, $ev);
    $evid = $evtmp->getValue("evt_idinitiator");
  }
  if ($evid > 0) {
    $event = new Doc($db, $evid);
    $ownerid = $event->getValue("CALEV_OWNERID", "");
    $ownertitle = $event->getValue("CALEV_OWNER", "");
    $evtitle  = $event->getValue("CALEV_EVTITLE", "");
    $evnote   = $event->getValue("CALEV_EVNOTE", "");
    $evstart  = db2date($event->getValue("CALEV_START", ""));
    $evend    = db2date($event->getValue("CALEV_END", ""));
    $evtype   = $event->getValue("CALEV_TIMETYPE", "");
    $evfreq   = $event->getValue("CALEV_FREQUENCY", 1);
    $evcal    = $event->getValue("CALEV_CALENDAR", -1);
    $evvis    = $event->getValue("CALEV_VISIBILITY", -1);
    $evalarm  = $event->getValue("CALEV_EVALARM", 0);
    $evalarmt = $event->getValue("CALEV_EVALARMTIME", 0);
    $evrepeat = $event->getValue("CALEV_REPEATMODE", 0);
    $evrweekd = $event->getTValue("CALEV_REPEATWEEKDAY", 0);
    $evrmonth = $event->getValue("CALEV_REPEATMONTH", 0);
    $evruntil = $event->getValue("CALEV_REPEATUNTIL", 0);
    $evruntild = db2date($event->getValue("CALEV_REPEATUNTILDATE"));
    $evrexcld  = $event->getTValue("CALEV_EXCLUDEDATE", array());
    $attendees = $event->getTValue("CALEV_ATTID", array());
    $attendeesState = $event->getTValue("CALEV_ATTSTATE", array());
    $attendeesGroup = $event->getTValue("CALEV_ATTGROUP", array());
    $evstatus = EVST_READ;
    $mailadd = "";
    $withme = false;
    $onlyme = true;
    foreach ($attendees as $k => $v) {
      if ($v == $action->user->fid) {
	$evstatus = ($evstatus == EVST_NEW ? EVST_READ : $attendeesState[$k]);
	$withme = true;
      } else {
        $onlyme = false;
	$u = new Doc($action->GetParam("FREEDOM_DB"), $v);
	$m = $u->getValue("US_MAIL");
	if ($m) $mailadd .= ($mailadd==""?"":", ").$u->getValue("US_FNAME")." ".$u->getValue("US_LNAME")." <".$m.">";
      }
    }
    $rwstatus = false;
    $ro = true;
    // Compute ro mode & rostatus mode
    if ($action->user->fid == $ownerid) {
      $rostatus = false;
      $ro = false;
    } else {
      $rostatus = true;
      foreach ($attendees as $k => $v) {
        if ($action->user->fid == $v) $rostatus = false;
      }
    }
  } else {
    $mailadd = "";
    $evtitle  = "";
    $evnote   = "";
    $evstart  = $times;
    $evend    = $timee;
    $evtype   = ($nh==1?2:0);
    $evfreq   = 1;
    $evcal    = -1;
    $evvis    = 0;
    $evalarm  = 0;
    $evalarmt = -1;
    $evrepeat = 0;
    $evrweekd = array();
    $evrmonth = 0;
    $evruntil = -1;
    $evruntild = $timee + (14*24*3600);
    $evrexcld  = array();
    $evstatus = EVST_ACCEPT;
    $withme = true;
    $attendees = array( );
    $attendeesState = array( );
    $attendeesGroup = array( );
    $userd = $action->GetParam("WGCAL_U_USERESSINEVENT", 0);
    if ($userd == 1) {
      $curress = $action->GetParam("WGCAL_U_RESSTMPLIST", $action->GetParam("WGCAL_U_RESSDISPLAYED", $action->user->id));
      $lress = explode("|", $curress);
      if (count($lress)>0) {
	foreach ($lress as $k => $v) {
	  $tt = explode("%", $v);
	  if ($tt[1] == 1) {
	    $dd = new Doc($db, $tt[0]);
	    if ($dd->fromid != getIdFromName($db,"SCALENDAR")) {
	      $attendees[$iatt] = $tt[0];
	      $attendeesState[$iatt] = EVST_NEW;
	      $attendeesGroup[$iatt] = -1;
	      $iatt++;
	    }
	  }
	}
      }
    }
    $userd = $action->GetParam("WGCAL_U_USEPREFRESSOURCES", 0);
    if ($userd == 1) {
      $curress = $action->GetParam("WGCAL_U_PREFRESSOURCES", "");
      $lress = explode("|", $curress);
      if (count($lress)>0) {
	foreach ($lress as $k => $v) {
	  if ($v=="") continue;
	  $dd = new Doc($db, $v);
	  if ($dd->fromid != getIdFromName($db,"SCALENDAR")) {
	    $attendees[$iatt] = $v;
	    $attendeesState[$iatt] = EVST_NEW;
	    $attendeesGroup[$iatt] = -1;
	    $iatt++;
	  }
	}
      }
    }
    $ownerid = $action->user->fid;
    $attru = GetTDoc($action->GetParam("FREEDOM_DB"), $ownerid);
    $ownertitle = $attru["title"];
    $rostatus = true;
    $ro = false;
  }

  $action->lay->set("ROMode", ($ro?"true":"false"));
  
  $action->lay->set("EVENTID", $evid);
  if ($evid==-1 || $ro) {
    $action->lay->setBlockData("EMPTY", null);
  } else {
    $action->lay->setBlockData("EMPTY", array( array("nop" => "") ));
    $action->lay->set("mailadd", $mailadd);
  }    
  $action->lay->set("DFMT", "%A %d %b %Y");

  EventSetTitle($action, $evtitle, $ro);
  EventSetDescr($action, $evnote, $ro);  
  EventSetDate($action, $evstart, $evend, $evtype, $ro);
  EventSetVisibility($action, $evvis, $ro);
  EventSetCalendar($action, $evcal, $ro);
  EventSetStatus($action, $evstatus, $withme, $onlyme, $rostatus);
  EventSetAlarm($action, $evalarm, $evalarmt, $ro);
  EventSetRepeat($action, $evrepeat, $evrweekd, $evrmonth, $evruntil, $evruntild, $evfreq, $evrexcld, $ro);
  EventAddAttendees($action, $ownerid, $attendees, $attendeesState, $attendeesGroup, $withme, $ro, $onlyme);
  EventSetOwner($action, $ownerid, $ownertitle);

  return;  
}    


function EventSetTitle(&$action, $title, $ro) {
  $action->lay->set("TITLE", $title);
  $action->lay->set("TITLERO", ($ro?"readonly":""));
}
function EventSetDescr(&$action, $text, $ro) {
  $action->lay->set("DESCR", $text);
  $action->lay->set("DESCRRO", ($ro?"readonly":""));
}


function EventSetDate(&$action,  $dstart, $dend, $type, $ro) 
{
  $action->lay->set("NOHOURINIT", ($type==1?"checked":""));
  $action->lay->set("NOHOURRO", ($ro?"disabled":""));
  $action->lay->set("NOHOURDISP", ($type==2?"hidden":"visible"));
  $action->lay->set("ALLDAYINIT", ($type==2?"checked":""));
  $action->lay->set("ALLDAYRO", ($ro?"disabled":""));
  $action->lay->set("ALLDAYDISP", ($type==1?"hidden":"visible"));
  if ($type==1 || $type==2) $action->lay->set("HVISIBLE", "hidden");
  else $action->lay->set("HVISIBLE", "visible");
  
  
  $start_y = strftime("%Y", $dstart);
  $start_m = strftime("%m", $dstart);
  $start_d = strftime("%d", $dstart);
  $lstart = mktime(0,0,0,$start_m,$start_d,$start_y);
  $action->lay->set("START", $lstart);
  $action->lay->set("mSTART", $lstart*1000);
  $action->lay->set("STARTREAD", strftime("%a %d %b %Y", $lstart));
  $action->lay->set("H_START", strftime("%H", $dstart));
  $th = array();
  for ($h=$action->getParam("WGCAL_U_HSUSED",7); $h<$action->getParam("WGCAL_U_HEUSED",19); $h++) {
    $th[$h]["optvalue"] = $h;
    $th[$h]["optdescr"] = (strlen($h)==1?"0".$h:$h)."h";
    $th[$h]["optselect"] = ($h==strftime("%H", $dstart)?"selected":"");
  }
  $action->lay->setBlockData("SHSEL", $th);
  $th = array();
  for ($h=0; $h<60; $h+=$action->getParam("WGCAL_U_MINCUSED",15)) {
    $th[$h]["optvalue"] = $h;
    $th[$h]["optdescr"] = (strlen($h)==1?"0".$h:$h);
    $th[$h]["optselect"] = ($h>=strftime("%M", $dend-60) && $h<=strftime("%M", $dend+240)?"selected":"");
  }
  $action->lay->setBlockData("SHMSEL", $th);
  $action->lay->set("M_START", strftime("%M", $dstart));
  $action->lay->set("FSTART", $dstart);
  
  $end_y = strftime("%Y", $dend);
  $end_m = strftime("%m", $dend);
  $end_d = strftime("%d", $dend);
  $lend = mktime(0,0,0,$end_m,$end_d,$end_y);
  $action->lay->set("END", $lend);
  $action->lay->set("mEND", $lend*1000);
  $action->lay->set("ENDREAD", strftime("%a %d %b %Y", $lend));
  $action->lay->set("H_END", strftime("%H", $dend));
  $action->lay->set("M_END", strftime("%M", $dend));
  $action->lay->set("FEND", $dend);
  $th = array();
  for ($h=$action->getParam("WGCAL_U_HSUSED",7); $h<$action->getParam("WGCAL_U_HEUSED",19); $h++) {
    $th[$h]["optvalue"] = $h;
    $th[$h]["optdescr"] = (strlen($h)==1?"0".$h:$h)."h";
    $th[$h]["optselect"] = ($h==strftime("%H", $dend)?"selected":"");
  }
  $action->lay->setBlockData("EHSEL", $th);
  $th = array();
  for ($h=0; $h<60; $h+=$action->getParam("WGCAL_U_MINCUSED",15)) {
    $th[$h]["optvalue"] = $h;
    $th[$h]["optdescr"] = (strlen($h)==1?"0".$h:$h);
    $th[$h]["optselect"] = ($h>=strftime("%M", $dend-60) && $h<=strftime("%M", $dend+240)?"selected":"");
  }
  $action->lay->setBlockData("EHMSEL", $th);
 
  if ($ro) {
    $action->lay->set("DATEBUTVIS", "none");
    $action->lay->set("DATERO", "disabled");
  } else {
    $action->lay->set("DATEBUTVIS", "");
    $action->lay->set("DATERO", "");
  }
  $action->lay->set("DATEVIS", (($allday || $nohour)?"none":""));
}

function EventSetVisibility(&$action, $vis, $ro) {
  $avis = CAL_getEventVisibilities($action->GetParam("FREEDOM_DB"), "");
  $ic = 0;
  $action->lay->set("evconfidentiality", $vis);
  foreach ($avis as $k => $v) {
    $tconf[$ic]["value"] = $k;
    $tconf[$ic]["descr"] = $v;
    $tconf[$ic]["selected"] = ($vis==$k?"selected":"");
    $ic++;
  }
  $action->lay->SetBlockData("RVCONFID", $tconf);
  $action->lay->set("rvvisro", ($ro?"disabled":""));
}
  
function EventSetCalendar(&$action, $cal, $ro) {
  $acal = WGCalGetMyCalendars($action, $action->GetParam("FREEDOM_DB"));
  $action->lay->set("evcalendar", $cal);
  $ic = 0;
  foreach ($acal as $k => $v) {
    $tconf[$ic]["value"] = $v[0];
    $tconf[$ic]["descr"] = $v[1];
    $tconf[$ic]["selected"] = ($cal==$v[0]?"selected":"");
    $ic++;
  }
  $action->lay->SetBlockData("CALS", $tconf);
  $action->lay->set("rvcalro", ($ro?"disabled":""));
}

function EventSetStatus(&$action, $status, $withme, $onlyme, $ro) {
  $acal = WGCalGetState($action->GetParam("FREEDOM_DB"), "");
  $action->lay->set("evstatus", $status);
  ////echo "Mode ".($ro?"ro":"rw")." OnlyMe=".($onlyme?"T":"F")." Withme=".($withme?"T":"F")." Status=".WGCalGetLabelState($status)."<br>";
  $ic = 0;
  if ($ro) {
    $tconf[$ic]["iState"] = $ic;
    $tconf[$ic]["vState"] = $status;
    $tconf[$ic]["rState"] = "disabled";
    $tconf[$ic]["tState"] = WGCalGetLabelState($status);
    $tconf[$ic]["cState"] = WGCalGetColorState($status);
    $tconf[$ic]["sState"] = "checked";
  } else {
    foreach ($acal as $k => $v) {
      if ($k==EVST_NEW || $k==EVST_READ) continue;
      $tconf[$ic]["iState"] = $k;
      $tconf[$ic]["vState"] = $k;
      $tconf[$ic]["rState"] = "";
      $tconf[$ic]["tState"] = WGCalGetLabelState($k);
      $tconf[$ic]["cState"] = WGCalGetColorState($k);
      $tconf[$ic]["sState"] = ($k==$status ? "checked" : "");
      $ic++;
    }
  }
  $action->lay->set("vStatus", ($withme&&!$onlyme ? "visible" : "hidden")); 
  $action->lay->SetBlockData("STATUSZ", $tconf);
  $action->lay->set("cState", WGCalGetColorState($status));
}
  
function EventSetAlarm(&$action, $alarm, $alarmt, $ro) {

  $action->lay->set("ALARMCHK", ($alarm?"checked":""));
  $action->lay->set("ALARMRO", ($ro?"disabled":""));
  $action->lay->set("ALRMVIS", ($alarm?"visible":"hidden"));

  if ($alarmt>0) {
    $H = floor($alarmt / 60);
    $M = $alarmt - ($H * 60);
  } else {
    $H = 1;
    $M = 0;
  }

  $inc = 15;
  for ($min=0; $min<60; $min+=$inc) {
    $r = ($min==0?0:($min/$inc));
    $m[$r]["ALRMPERIOD_V"] = $min;
    $m[$r]["ALRMPERIOD_S"] = ($M>=$min && $min<($min+$inc)?"selected":"");
  } 
  $action->lay->SetBlockData("ALARM_MIN", $m);

  for ($hour=0; $hour<24; $hour++) {
    $h[$hour]["ALRMPERIOD_V"] = $hour;
    $h[$hour]["ALRMPERIOD_S"] = ($H==$hour?"selected":"");
  } 
  $action->lay->SetBlockData("ALARM_HR", $h);
}

function EventSetRepeat(&$action, $rmode, $rday, $rmonthdate, $runtil,
			$runtildate, $freq, $recxlude = array(), $ro = false )
{

  $action->lay->set("REPEAT_SELECTED", "");
  $action->lay->set("FREQVALUE", $freq);
  
  for ($i=0; $i<=4; $i++) $action->lay->set("REPEATTYPE_".$i, ($rmode==$i?"checked":""));

  $tday = array( _("monday"), _("tuesday"),_("wenesday"),_("thursday"),_("friday"),_("saturday"), _("sunday"));
  for ($i=0; $i<=6; $i++) {
    $td[$i]["dayn"] = $i;
    $td[$i]["repeatdis"] = ($ro?"disabled":"");
    $td[$i]["tDay"] = $tday[$i];
    $td[$i]["rdstate"] = "";
  }
  foreach ($rday as $kd => $vd) $td[$vd]["rdstate"] = "checked";
  $action->lay->SetBlockData("D_RWEEKDISPLAY", $td);

  $action->lay->set("RWEEKDISPLAY", ($rmode==2?"":"none"));

  $action->lay->set("D_RMONTH", ($rmode==3?"":"none"));
  $action->lay->set("D_RMONTH_DATE_CHECKED", ($rmonthdate==0?"checked":""));
  $action->lay->set("D_RMONTH_DAY_CHECKED", ($rmonthdate==1?"checked":""));
  
  $action->lay->set("D_RUNTIL_INFI", ($runtil==0?"checked":""));
  $action->lay->set("D_RUNTIL_DATE", ($runtil==1?"checked":""));
  $action->lay->set("RUNUNTIL_DATE_DISPLAY", ($runtil==1?"":"none"));
  
  $action->lay->set("uDate", strftime("%A %d %b %Y", $runtildate));
  $action->lay->set("umDate", $runtildate*1000);
  

  // Excluded dates
  if (is_array($recxlude) && count($recxlude)>0) {
    $ide = 0;
    foreach ($recxlude as $kd => $vd) {
      if ($vd!="" && $vd>0) {
        $ld = db2date($vd);
        $rx[$ide]["rDate"] = strftime("%a %d %b %Y", $ld);
        $rx[$ide]["mDate"] = $ld;
        $rx[$ide]["iDate"] = $i;
	$ide++;
      }
    }
    if ($ide>0) $action->lay->setBlockData("EXCLDATE", $rx);
  }
  $action->lay->set("repeatvie", ($ro?"none":""));
  $action->lay->set("repeatdis", ($ro?"disabled":""));
  
}

function EventSetOwner(&$action, $ownerid, $ownertitle) {
  $action->lay->set("ownerid", $ownerid);
  $action->lay->set("ownertitle", $ownertitle);
}

function EventAddAttendees(&$action, $ownerid, $attendees = array(), $attendeesState = array(), $attendeesGroup = array(), $withme=true, $ro=false, $onlyme) {
//echo "ownerid = $ownerid cuser = ".$action->user->fid." withme = ".($withme?"T":"F")."<br>";
  $udbaccess = $action->GetParam("COREUSER_DB");
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $ugrp = new User($udbaccess);
  $groupfid = getIdFromName($dbaccess, "GROUP");
  $igroupfid = getIdFromName($dbaccess, "IGROUP");
  $att = array();
  $a = 0;
  $tallgrp = array(); $grp = 0;
  foreach ($attendees as $k => $v) {
    if ($v == "" || $v==0 || ($ownerid==$action->user->fid&&$action->user->fid==$v) ) continue;
    if ($attendeesGroup[$k] != -1) continue;
    $res = new Doc($dbaccess, $v);
    $att[$a]["attId"]    = $v;
    $att[$a]["attState"] = $attendeesState[$k];
    $att[$a]["attTitle"] = $res->title;
    $att[$a]["attIcon"]  = $res->GetIcon();
    if ($res->fromid==$groupfid || $res->fromid==$igroupfid) {
      $ulist = $ugrp->GetUsersGroupList($res->getValue("US_WHATID"));
      $tugrp = array(); $rgrp = 0;
      foreach ($ulist as $ku=>$vu) {
	$rg = new Doc($dbaccess, $vu["fid"]);
        if ($rg->fromid==$groupfid || $rg->fromid==$igroupfid) continue;
	$tugrp[$rgrp]["atticon"] = $rg->GetIcon();;
	$tugrp[$rgrp]["atttitle"] = $rg->title;
	$cstate = "?";
	foreach ($attendees as $katt => $vatt) {
	  if ($vatt==$rg->id) $cstate = WGCalGetLabelState($attendeesState[$katt]);
	}
	$tugrp[$rgrp]["attstate"] = $cstate;
	$rgrp++;
      }
      $tallgrp[$grp]["GROUPCONTENT"] = "GROUPCONTENT$v";
      $action->lay->SetBlockData($tallgrp[$grp]["GROUPCONTENT"], $tugrp);
      $tallgrp[$grp]["RID"] = $v;
      $tallgrp[$grp]["groupicon"] = $res->getIcon();
      $tallgrp[$grp]["grouptitle"] = $res->title;
      $grp++;
      $att[$a]["attLabel"] = "";
      $att[$a]["attColor"] = "transparent";
    } else {
      $att[$a]["attLabel"] = WGCalGetLabelState($attendeesState[$k]);
      $att[$a]["attColor"] = WGCalGetColorState($attendeesState[$k]);
    }
    $a++;
  }
  $action->lay->setBlockData("GROUPS", $tallgrp);
  if ($a==0) {
    $action->lay->set("voneatt", "none");
    $action->lay->set("vnatt", "none");
  } else {
    $action->lay->set("voneatt", "");
    $action->lay->set("vnatt", "");
  }
  if ($ro) $action->lay->set("voneatt", "none");

  $action->lay->set("vnatt", "none");
  $action->lay->set("WITHME", "");
  $action->lay->set("WITHMERO", ($ro?"disabled":""));
  if ($ownerid==$action->user->fid) {
    if (!$onlyme) $action->lay->set("vnatt", "");
    $action->lay->set("WITHME", ($withme?"checked":""));
  }
  $action->lay->setBlockData("ADD_RESS", $att);
  $action->lay->set("attendeesro", ($ro?"none":""));
}


function db2date($i) {
  $i = preg_replace( '/(\d{2})\W(\d{2})\W(\d{4}|\d{4})\W(\d{2}:\d{2})/', '$2/$1/$3 $4', $i);
  $d = strtotime($i);
  return $d;
}

?>
