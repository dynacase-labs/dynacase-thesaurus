<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_editevent.php,v 1.24 2005/02/08 18:04:23 marc Exp $
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
  $evid = GetHttpVars("evt", -1);
  
  if ($evid > 0) {
    $event = new Doc($db, $evid);
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
    $evrweekd = $event->getValue("CALEV_REPEATWEEKDAY", 0);
    $evrmonth = $event->getValue("CALEV_REPEATMONTH", 0);
    $evruntil = $event->getValue("CALEV_REPEATUNTIL", 0);
    $evruntild = db2date($event->getValue("CALEV_REPEATUNTILDATE"));
    $evrexcld  = $event->getTValue("CALEV_EXCLUDEDATE", array());
    $attendees = $event->getTValue("CALEV_ATTID", array());
    $attendeesState = $event->getTValue("CALEV_ATTSTATE", array());
    $evstatus = 0;
    $mailadd = "";
    $withme = false;
    foreach ($attendees as $k => $v) {
      if ($v == $action->user->fid) {
	$evstatus = ($evstatus == EVST_NEW ? EVST_READ : $evstatus);
	$withme = true;
      } else {
	$u = new Doc($action->GetParam("FREEDOM_DB"), $v);
	$m = $u->getValue("US_MAIL");
	if ($m) $mailadd .= ($mailadd==""?"":", ").$u->getValue("US_FNAME")." ".$u->getValue("US_LNAME")." <".$m.">";
      }
    }
    $ownerid = $event->getValue("CALEV_OWNERID", "");
    $ownertitle = $event->getValue("CALEV_OWNER", "");
    $rwstatus = false;
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
    $evrweekd = 0;
    $evrmonth = 0;
    $evruntil = -1;
    $evruntild = $timee + (14*24*3600);
    $evrexcld  = array();
    $evstatus = EVST_ACCEPT;
    $withme = true;
    $attendees = array( );
    $attendeesState = array( );
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
	      $iatt++;
	    }
	  }
	}
      }
    }
    $ownerid = $action->user->fid;
    $attru = GetTDoc($action->GetParam("FREEDOM_DB"), $ownerid);
    $ownertitle = $attru["title"];
    $rostatus = true;
  }

  // Compute ro mode & rostatus mode
  $ro = true;
  if ($action->user->fid == $ownerid) $ro = false;
  $action->lay->set("ROMode", ($ro?"true":"false"));
  $rostatus = true;
  if ($evid!=-1) {
    foreach ($attendees as $k => $v) {
      if ($action->user->fid == $v) $rostatus = false;
    }
  }
  
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
  EventSetStatus($action, $evstatus, $rostatus);
  EventSetAlarm($action, $evalarm, $evalarmt, $ro);
  EventSetRepeat($action, $evrepeat, $evrweekd, $evrmonth, $evruntil, $evruntild, $evfreq, $evrexcld, $ro);
  EventAddAttendees($action, $attendees, $attendeesState, $withme, $ro);
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

function EventSetStatus(&$action, $status, $ro) {
  $acal = WGCalGetState($action->GetParam("FREEDOM_DB"), "");
  $action->lay->set("evstatus", $status);
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
  $action->lay->SetBlockData("STATUSZ", $tconf);
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

  $action->lay->set("D_RWEEKDISPLAY", ($rmode==2?"":"none"));
  for ($i=1; $i<=7; $i++)  $action->lay->set("D_RWEEKDISPLAY_".$i, ($rday==$i?"checked":""));

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
        $rx[]["rDate"] = strftime("%a %d %b %Y", $vd);
        $rx[]["mDate"] = $vd;
        $rx[]["iDate"] = $i;
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

function EventAddAttendees(&$action, $attendees = array(), $attendeesState = array(), $withme=true, $ro=false) {
  $att = array();
  $a = 0;
  $doc = new Doc($action->GetParam("FREEDOM_DB"));
  foreach ($attendees as $k => $v) {
    if ($v == "" || $v==0 || $v == $action->user->fid) continue;
    $att[$a]["attId"]    = $v;
    $att[$a]["attState"] = $attendeesState[$k];
    $attru = GetTDoc($action->GetParam("FREEDOM_DB"), $v);
    $att[$a]["attTitle"] = $attru["title"];
    $att[$a]["attIcon"]  = $doc->GetIcon($attru["icon"]);
    $a++;
  }
  if ($a==0) {
    $action->lay->set("voneatt", "none");
    $action->lay->set("vnatt", "none");
  } else {
    $action->lay->set("voneatt", "");
    $action->lay->set("vnatt", "");
  }
  if ($ro) $action->lay->set("voneatt", "none");
  $action->lay->setBlockData("ADD_RESS", $att);
  $action->lay->set("attendeesro", ($ro?"none":""));
  $action->lay->set("WITHME", ($withme?"checked":""));
  $action->lay->set("WITHMERO", ($ro?"disabled":""));
}


function db2date($i) {
  $i = preg_replace( '/(\d{2})\W(\d{2})\W(\d{4}|\d{4})\W(\d{2}:\d{2})/', '$2/$1/$3 $4', $i);
  $d = strtotime($i);
  return $d;
}

?>
