<?php

include_once("FDL/Class.Doc.php");

function wgcal_storeevent(&$action) {

  global $_POST;
  print_r2($_POST);

  $ev->id  = GetHttpVars("eventid", -1);
  $ev->title = GetHttpVars("rvtitle", "");
  $ev->allday = (GetHttpVars("allday", "") == "on" ? true : false );
  $ev->nohour = (GetHttpVars("nohour", "") == "on" ? true : false );
  $ev->start = GetHttpVars("rvstart", -1);
  $ev->end = GetHttpVars("rvend", -1);
  $ev->note = GetHttpVars("rvnote", "");
  $ev->visib = wgcal_getArrayVal("rvconfid", 0);
  $ev->calendar = wgcal_getArrayVal("rvcalendar", 0);
  $ev->state = wgcal_getArrayVal("rvstatus", 0);
  $ev->alarm = (GetHttpVars("AlarmCheck", "") == "on" ? true : false );
  $ev->alarm_h = GetHttpVars("alarmhour", 0);
  $ev->alarm_m = GetHttpVars("alarmmin", 0);

  $ev->repeatmode = GetHttpVars("repeattype", -1);
  $ev->rweekday = GetHttpVars("rweekday", -1);
  $ev->rmonth = GetHttpVars("rmonth", -1);
  $ev->runtil = GetHttpVars("runtil", -1);
  $ev->runtildate = GetHttpVars("runtildate", -1);
  
  $db = $action->getParam("FREEDOM_DB");
  if ($ev->id==-1) {
    $event = new Doc($db);
    $event = createDoc($db, "CALEVENT");
    $err = $event->Add();
  } else {
    $event = new Doc($db, $ev->id);
  }
  $event->setValue("CALEV_EVTITLE", $ev->title);
  $event->setValue("CALEV_EVNOTE", $ev->note);
  $err = $event->Modify();
  echo "<h2> Erreur : $err </h2>";
  print_r2($event);
  
    

}

function wgcal_getArrayVal($key, $def=null) {
  $v = $def;
  $a = GetHttpVars($key, array());
  if (count($a)>0) $v = $a[0];
  return $v;
}

?>