<?php

include_once("FDL/Class.Doc.php");

function wgcal_storeevent(&$action) {

  $db = $action->getParam("FREEDOM_DB");
  
  //   global $_POST;
  //   print_r2($_POST);

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
  
  $event->setValue("CALEV_EVCALENDARID", GetHttpVars("evcalendar"));
  $event->setValue("CALEV_VISIBILITY", GetHttpVars("evconfidentiality"));
  
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
  $oldatt_id    = $event->getTValue("CALEV_ATTID", array());
  $oldatt_state = $event->getTValue("CALEV_ATTSTATE", array());
  $attendees = GetHttpVars("attendees", array());
  $attendeesname = array();
  $attendeesstate = array();
  $cstatus = GetHttpVars("rvstatus");
  foreach ($attendees as $ka => $va) {
    if ($va<=0||$va=="") continue;
    $att = new Doc($db, $va);
    $attendeesname[$ka] = $att->title;
    if ($action->user->fid == $va) {
      $attendeesstate[$ka] = $cstatus;
    }  else {
      $k = array_search($va, $oldatt_id);
      if ($k) $attendeesstate[$ka] = $oldatt_state[$k];
      else $attendeesstate[$ka] = 0;
    }
    //echo "ressource #".$attendees[$ka]." [".$attendeesname[$ka]."] status =  ".$attendeesstate[$ka]."<br>";
  }
  $event->setValue("CALEV_ATTID", $attendees); 
  $event->setValue("CALEV_ATTTITLE", $attendeesname); 
  $event->setValue("CALEV_ATTSTATE", $attendeesstate); 
    
  $err = $event->Modify();
   if ($err!="") AddWarningMsg("$err");
   else {
     $err = $event->PostModify();
     if ($err!="") AddWarningMsg("$err");
   }
  
  $changed = true;
  // if ($changed) mail_rv($action, $event);

  redirect($action, "WGCAL","WGCAL_CALENDAR");
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



function mail_rv(&$action, $event) {

  $from = $event->getValue("CALEV_OWNER");
  $mailadd = "";
  $att = $event->getTValue("CALEV_ATTID", array()); 
  foreach ($att as $k => $v) {
    $u = new Doc($action->GetParam("FREEDOM_DB"), $v);
    $ma = $u->getValue("US_MAIL");
    if ($ma!="") {
      print_r2($ma);
      $mailadd .= ($mailadd==""?"":", ") . $u->getValue("US_FNAME"). " " .$u->getValue("US_LNAME"). " <".$ma.">";
    }
  }
  if ($to=!"") {

    $m = new Layout( "WGCAL/Layout/mail_rv_".$action->GetParam("CORE_LANG", "fr_FR").".xml", 
		     $action );
    $m->set("rvowner", $event->getValue("CALEV_OWNER"));
    $m->set("rvtitle", $event->getValue("CALEV_TITLE"));
    $m->set("dstart",  $event->getValue("CALEV_START"));
    $m->set("dstart",  $event->getValue("CALEV_END"));
    $out = $m->gen();
    $tmpf = uniqid("/tmp/rv".$doc->id);
    $fout = fopen($tmpf,"w");
    fwrite($fout,$out);
    fclose($fout);
    
    $icsf = makeIcsFile($action, $event);

    $subject = "[proposition de rendez-vous | meeting proposal] ".$event->getValue("CALEV_TITLE");
    $cmd = "metasend  -b -S 4000000 -c '$from' -F '$from' -t '$mailadd' -s \"$subject\"  ";
    $cmd .= " -m 'text/plain' -e 'quoted-printable' -i event -f '$tmpf' ";
    $cmd .= " -m 'text/html' -e 'quoted-printable' -i ICS -f '$icsf' ";
    
    $cmd = "export LANG=C;".$cmd;
    echo "<div style=\"background:red\"> $cmd <div>";
    system ($cmd, $status);
    unlink($fout);
    unlink($icsf);
  }
}

function makeIcsFile(&$action, $event) {
  $m = new Layout( "WGCAL/Layout/event.ics", $action );
  $tmpf = uniqid("/tmp/event".$doc->id.".ics");
  $fout = fopen($tmpf,"w");
  $out = $m->gen();
  fwrite($fout,$out);
  fclose($fout);
  return $tmpf;
}


?>