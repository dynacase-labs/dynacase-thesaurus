<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_editevent.php,v 1.8 2005/01/14 06:34:02 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

include_once("FDL/Class.Doc.php");
include_once("WGCAL/WGCAL_calevent.php");

define("FDATE", "%A %d %b %Y");

function wgcal_editevent(&$action) {

  $db = $action->getParam("FREEDOM_DB");

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/DHTMLapi.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/PopupWindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/ColorPicker2.js");

  $action->parent->AddJsRef("jscalendar/Layout/calendar.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-setup.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-fr.js");

  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_editevent.js");
  
  $cssfile = $action->GetLayoutFile("calendar-default.css");
  $csslay = new Layout($cssfile,$action);
  $action->parent->AddCssCode($csslay->gen());
  

  $time = GetHttpVars("time", time());
  $evid = GetHttpVars("evt", -1);
  
  if ($evid != -1) {
    $event = new Doc($db, $evid);
    $evtitle  = $event->getValue("CALEV_EVTITLE", "");
    $evnote   = $event->getValue("CALEV_EVNOTE", "");
    $evstart  = $event->getValue("CALEV_START", "");
    $evend    = $event->getValue("CALEV_SEND", "");
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
    $evruntild = $event->getValue("CALEV_REPEATUNTILDATE", 0);
    $evrexcld  = $event->getTValue("CALEV_EXCLUDEDATE", array());
    $rwstatus = false;
  } else {
    $evtitle  = "";
    $evnote   = "";
    $evstart  = $time;
    $evend    = $time+3600;
    $evtype   = 0;
    $evfreq   = 1;
    $evcal    = -1;
    $evvis    = 0;
    $evalarm  = 0;
    $evalarmt = -1;
    $evrepeat = 0;
    $evrweekd = 0;
    $evrmonth = 0;
    $evruntil = -1;
    $evruntild = 0;
    $evrexcld  = array();
    $rwstatus = true;
  }

  $action->lay->set("EVENTID", $evid);
  $action->lay->set("DFMT", FDATE);

  // Compute rw mode & rwstatus mode
  $rw = false;

  EventSetTitle($action, $evtitle, $rw);
  EventSetDescr($action, $evnote, $rw);  
  EventSetDate($action, $evstart, $evend, $evtype, $rw);
  EventSetVisibility($action, $evvis, $rw);

  EventSetCalendar($action, $evcal, $rw);
  EventSetStatus($action, $evstatus, $rwstatus);

  EventSetAlarm($action, $evalarm, $evalarmt, $rw);

  EventSetRepeat($action, $evrepeat, $evrweekd, $evrmonth, $evruntil, $evruntild, $evfreq, $evrexcld, $rw);

  // $attendees = array(
// 		     array("id" => 1018, "title" => "Eric Brison", "icon" => "http://127.0.0.1/what/Images/personne.gif"),
// 		     array("id" => 1019, "title" => "Marc Claverie", "icon" => "http://127.0.0.1/what/Images/personne.gif") );
  $attendees = array();
  EventAddAttendees($action, $attendees, $rw);
  
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

  $action->lay->set("ALLDAY", ($type==1?1:0));
  $action->lay->set("ALLDAYRO", ($ro?"disabled":""));
  $action->lay->set("NOHOUR", ($type==2?1:0));
  $action->lay->set("NOHOURRO", ($ro?"disabled":""));
  
  $action->lay->set("START", $dstart);
  $action->lay->set("mSTART", $dstart*1000);
  $action->lay->set("STARTREAD", strftime(FDATE, $dstart));
  $action->lay->set("H_START", strftime("%H", $dstart));
  $action->lay->set("M_START", strftime("%M", $dstart));
  
  $action->lay->set("END", $dend);
  $action->lay->set("mEND", $dend*1000);
  $action->lay->set("ENDREAD", strftime(FDATE, $dend));
  $action->lay->set("H_END", strftime("%H", $dend));
  $action->lay->set("M_END", strftime("%M", $dend));
 
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
  $acal = CAL_getCalendars($action->GetParam("FREEDOM_DB"), $action->user->id);
  $ic = 0;
  foreach ($acal as $k => $v) {
    $tconf[$ic]["value"] = $k;
    $tconf[$ic]["descr"] = $v;
    $tconf[$ic]["selected"] = ($cal==$k?"selected":"");
    $ic++;
  }
  $action->lay->SetBlockData("CALS", $tconf);
  $action->lay->set("rvcalro", ($ro?"disabled":""));
}

function EventSetStatus(&$action, $status, $ro) {
  $acal = CAL_getEventStates($action->GetParam("FREEDOM_DB"), "");
  $ic = 0;
  foreach ($acal as $k => $v) {
    $tconf[$ic]["value"] = $k;
    $tconf[$ic]["descr"] = $v;
    $tconf[$ic]["selected"] = ($status==$k?"selected":"");
    $ic++;
  }
  $action->lay->SetBlockData("RVSTATUS", $tconf);
  $action->lay->set("rvstaro", ($ro?"disabled":""));
}
  
function EventSetAlarm(&$action, $alarm, $alarmt, $ro) {

  $action->lay->set("ALARM", ($alarm?1:0));
  $action->lay->set("ALARMRO", ($ro?"disabled":""));
  $action->lay->set("ALRMVIS", ($alarm?"none":""));

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

  $action->lay->set("D_RWEEKDISPLAY", ($rmode==1?"":"none"));
  for ($i=1; $i<=7; $i++)  $action->lay->set("D_RWEEKDISPLAY_".$i, ($rday==$i?"checked":""));

  $action->lay->set("D_RMONTH", ($rmode==2?"":"none"));
  $action->lay->set("D_RMONTH_DATE_CHECKED", ($rmonthdate==0?"checked":""));
  $action->lay->set("D_RMONTH_DAY_CHECKED", ($rmonthdate==1?"checked":""));
  
  $action->lay->set("D_RUNTIL_INFI", ($runtil==0?"checked":""));
  $action->lay->set("D_RUNTIL_DATE", ($runtil==1?"checked":""));
  $action->lay->set("RUNUNTIL_DATE_DISPLAY", ($runtil==1?"":"none"));
  
  $action->lay->set("uDate", strftime(FDATE, $runtildate));
  $action->lay->set("umDate", $runtildate*1000);
  

  // Excluded dates
  $action->lay->setBlockData("EXCLDATE", null);

  $action->lay->set("repeatvie", ($ro?"none":""));
  $action->lay->set("repeatdis", ($ro?"disabled":""));
  
}

function EventAddAttendees(&$action, $attendees = array(), $ro = false) {
  $att = array();
  $a = 0;
  foreach ($attendees as $k => $v) {
    $att[$a]["attId"] = $v["id"];
    $att[$a]["attTitle"] = $v["title"];
    $att[$a]["attIcon"] = $v["icon"];
    $a++;
  }
  $action->lay->setBlockData("ADD_RESS", $att);
  $action->lay->set("attendeesro", ($ro?"none":""));
}
?>