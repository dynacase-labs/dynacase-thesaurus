<?php

include_once("FDL/Class.Doc.php");

function wgcal_storeevent(&$action) {

  $db = $action->getParam("FREEDOM_DB");
  
//   global $_POST;
//   print_r2($_POST);
//   exit;

  $id  = GetHttpVars("eventid", -1);
  if ($id==-1) {
    $event = createDoc($db, "CALEVENT");
    $event->Add();
  } else {
    $event = new Doc($db, $id);
  }
  
  $event->setValue("CALEV_OWNERID", GetHttpVars("ownerid", -1));
  $event->setValue("CALEV_OWNER", GetHttpVars("ownertitle", ""));

  $event->setValue("CALEV_EVTITLE", GetHttpVars("rvtitle", ""));
  $event->setValue("CALEV_EVNOTE", GetHttpVars("rvnote", ""));
  
  $ds = GetHttpVars("rvstart", 0);
  $de = GetHttpVars("rvend", 0);
  
  $event->setValue("CALEV_TIMETYPE", 0);
  $event->setValue("CALEV_START", date2db($ds));
  $event->setValue("CALEV_END", date2db($de));
  if (GetHttpVars("allday", "") == "on") {
    $event->setValue("CALEV_TIMETYPE", 2);
    $event->setValue("CALEV_START", date2db($ds, false)." 00:00");
    $event->setValue("CALEV_END", date2db($ds, false)." 23:59");
  }
  if (GetHttpVars("nohour", "") == "on") {
    $event->setValue("CALEV_TIMETYPE", 1);
    $event->setValue("CALEV_START", date2db($ds, false)." 00:00");
    $event->setValue("CALEV_END", date2db($ds, false)." 00:00");
  }
  
  $event->setValue("CALEV_FREQUENCY", GetHttpVars("frequency",1));
  
  $event->setValue("CALEV_EVCALENDAR", wgcal_getArrayVal("rvcalendar", 0));
  
  $event->setValue("CALEV_VISIBILITY", wgcal_getArrayVal("rvconfid", 0));
  
  $event->setValue("CALEV_EVALARM", (GetHttpVars("AlarmCheck", "")=="on"?1:0));
  if (GetHttpVars("AlarmCheck", "")=="on") {
    $event->setValue("CALEV_EVALARM", 1);
    $alarm = GetHttpVars("alarmhour", 0)*60 + GetHttpVars("alarmmin", 0);
    $event->setValue("CALEV_EVALARMTIME", ($alarm>0?$alarm:30));
  } else {
    $event->setValue("CALEV_EVALARM", 0);
    $event->setValue("CALEV_EVALARMTIME", 0);
  }
  
  // repeat 
  $rmode = GetHttpVars("repeattype", 0);
  $event->setValue("CALEV_REPEATMODE", GetHttpVars("repeattype", 0));
  $event->setValue("CALEV_REPEATWEEKDAY", GetHttpVars("rweekday", -1));
  $event->setValue("CALEV_REPEATMONTH", GetHttpVars("rmonth", 0));
  $event->setValue("CALEV_REPEATUNTIL", GetHttpVars("runtil", 0));
  $date = GetHttpVars("Vruntildate");
  if ($date>0) $sdate = $event->setValue("CALEV_REPEATUNTILDATE", date2db($date));
  $excl = array();
  foreach (GetHttpVars("listexcldate", array()) as $k => $v) {
    $excl[] = date2db($v);
  }
  $event->setValue("CALEV_T_EXCLUDEDATE", $excl);
  

  // Attendees
  $attendees = GetHttpVars("attendees", array());
  $attendeesname = array();
  $attendeesstate = array();
  foreach ($attendees as $ka => $va) {
    $att = new Doc($db, $va);
    $attendeesname[] = $att->title;
    $attendeesstate[] = 0;
  }
  $event->setValue("CALEV_ATTID", $attendees); 
  $event->setValue("CALEV_ATTTITLE", $attendeesname); 
  $event->setValue("CALEV_ATTSTATE", $attendeesstate); 
    
  // Compute global status according attendees one	   
  // $ev->state = wgcal_getArrayVal("rvstatus", 0);
  //  print_r2($event->getValues());		   
  $err = $event->Modify();
 
  if ($err!="") AddWarningMsg("$err");
  else redirect($action, "WGCAL","WGCAL_EDITEVENT&evt=".$event->id);
}

function wgcal_getArrayVal($key, $def=null) {
  $v = $def;
  $a = GetHttpVars($key, array());
  if (count($a)>0) $v = $a[0];
  return $v;
}
function date2db($d, $hm = true) {
  $fmt = ($hm ? "%d/%m/%Y %H:%M" : "%d/%m/%Y" );
  $s = strftime($fmt, $d);
  return $s;
}

?>