<?php

include_once("WHAT/Lib.Common.php");
include_once("FDL/Class.Doc.php");

function wgcal_storeevent(&$action) {

  $db = $action->getParam("FREEDOM_DB");
  
//      global $_POST;
//      print_r2($_POST);
//      return;

  $err = "";
  $id  = GetHttpVars("eventid", -1);
  if ($id==-1) {
    $event = createDoc($db, "CALEVENT");
    $event->Add();
  } else {
    $event = new Doc($db, $id);
  }
  
  $owner = GetHttpVars("ownerid", -1);
  $ownertitle = GetHttpVars("ownertitle", "");
  $event->setValue("CALEV_OWNERID", $owner);
  $event->setValue("CALEV_OWNER", $ownertitle);

  $ds = GetHttpVars("Fstart", 0);
  $de = GetHttpVars("Fend", 0);
  $evstatus = GetHttpVars("evstatus", EVST_NEW);

  $event->setValue("CALEV_EVTITLE", GetHttpVars("rvtitle"));
  $event->setValue("CALEV_EVNOTE", GetHttpVars("rvnote", ""));
  
  $event->setValue("CALEV_TIMETYPE", 0);
  $event->setValue("CALEV_START", date2db($ds));
  $event->setValue("CALEV_END", date2db($de));
  
  if (GetHttpVars("nohour", "") == "on") {
    $event->setValue("CALEV_TIMETYPE", 1);
    $event->setValue("CALEV_START", date2db($ds, false)." 00:00");
    $event->setValue("CALEV_END", date2db($ds, false)." 00:00");
  }
  if (GetHttpVars("allday", "") == "on") {
    $event->setValue("CALEV_TIMETYPE", 2);
    $event->setValue("CALEV_START", date2db($ds, false)." 00:00");
    $event->setValue("CALEV_END", date2db($ds, false)." 23:59");
  }
  
  $event->setValue("CALEV_FREQUENCY", GetHttpVars("frequency",1));
  
  $calid = GetHttpVars("evcalendar", -1);
  $event->setValue("CALEV_EVCALENDARID", $calid);
  $caltitle = _("main calendar");
  if ($calid>0) {
    $cal = new Doc($db, $calid);
    $caltitle = $cal->title;
  }
  $event->setValue("CALEV_EVCALENDAR", $caltitle);

  $event->setValue("CALEV_VISIBILITY", GetHttpVars("evconfidentiality", 0));
  
  $event->setValue("CALEV_EVALARM", (GetHttpVars("AlarmCheck", "")=="on"?1:0));
  if (GetHttpVars("AlarmCheck", "")=="on") {
    $event->setValue("CALEV_EVALARM", 1);
    $alarm = GetHttpVars("alarmhour", 0)*60 + GetHttpVars("alarmmin", 0);
    $event->setValue("CALEV_EVALARMTIME", ($alarm>0?$alarm:60));
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
  $excl = GetHttpVars("excludedate", "");
  if ($excl != "") {
    $excludedate = explode("|",$excl);
    foreach ($excludedate as $kd => $vd) $tex[] = date2db($vd);
    $event->setValue("CALEV_EXCLUDEDATE", $excludedate);
  }
  

  // --------------------------------------------------------------------------------------------------
  // Attendees
  $udbaccess = $action->GetParam("COREUSER_DB");
  $ugrp = new User($udbaccess);
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $groupfid = getIdFromName($dbaccess, "GROUP");
  $igroupfid = getIdFromName($dbaccess, "IGROUP");

  $withme = GetHttpVars("withMe", "off");
  $oldatt_id    = $event->getTValue("CALEV_ATTID", array());
  $oldatt_state = $event->getTValue("CALEV_ATTSTATE", array());
  $oldatt_group = $event->getTValue("CALEV_ATTGROUP", array());

  $attl = GetHttpVars("attendees", "");
  if ($attl!="") {
    $attendees = explode("|", $attl);
    
    $nattl = array(); $iatt = 0;
    
    // first, find all groups and expand it
    foreach ($attendees as $ka => $va) {
      $att = new Doc($db, $va);
      if ($att->fromid==$groupfid || $att->fromid==$igroupfid) {
	$nattl[$iatt]["fid"] = $att->id;
	$nattl[$iatt]["fgid"] = -1;
	$iatt++;
	$ulist = $ugrp->GetRUsersList($att->getValue("US_WHATID"));
	foreach ($ulist as $ku=>$vu) {
	  $nattl[$iatt]["fid"] = $vu["fid"];
	  $nattl[$iatt]["fgid"] = $att->id;
	  $iatt++;
	}
      }
    }

    // Look at others attendees
    foreach ($attendees as $ka => $va) {
      $att = new Doc($db, $va);
      if ($att->fromid!=$groupfid && $att->fromid!=$igroupfid) {
	$nattl[$iatt]["fid"] = $att->id;
	$nattl[$iatt]["fgid"] = -1;
	$iatt++;
      }
    }
//     print_r2($nattl);

    $attendeesid    = array();
    $attendeesname  = array();
    $attendeesstate = array();
    $attendeesgroup = array();
    $attcnt = 0;
    foreach ($nattl as $ka => $va) {
      if ($va<=0||$va=="") continue;
      $att = new Doc($db, $va["fid"]);
      $attendeesid[$attcnt]  = $att->id;
      $attendeesname[$attcnt]  = $att->title;
      $attendeesgroup[$attcnt] = $va["fgid"];
      $attendeesstate[$attcnt] = 0;
      if ($att->id == $action->user->fid) $attendeesstate[$attcnt] = $evstatus;
      else {
	foreach ($oldatt_id as $ko => $vo) {
	  if ($vo == $va["fid"]) $attendeesstate[$attcnt] = $oldatt_state[$ko];
	}
      }
      $attcnt++;
    }
  }
  
  if ($withme == "on" && $owner==$action->user->fid) {
    $attendeesname[$attcnt] = $action->user->lastname." ".$action->user->firstname;
    $attendeesid[$attcnt] = $action->user->fid;
    $attendeesstate[$attcnt] = $evstatus;
    $attendeesgroup[$attcnt] = -1;
  }
    
//   foreach ($attendeesid as $ka => $va)   echo "<p>[".$va." name:".$attendeesname[$ka]." state:".$attendeesstate[$ka]." group:".$attendeesgroup[$ka];

  $event->setValue("CALEV_ATTID", $attendeesid); 
  $event->setValue("CALEV_ATTTITLE", $attendeesname); 
  $event->setValue("CALEV_ATTSTATE", $attendeesstate); 
  $event->setValue("CALEV_ATTGROUP", $attendeesgroup); 
    
  $event->Modify();
  $event->PostModify();
  if ($err!="") AddWarningMsg("$err");
  
  $changed = true;
  //if ($changed) mail_rv($action, $event);
  redirect($action, "WGCAL", "WGCAL_CALENDAR");
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

  $fid = $event->getValue("CALEV_OWNERID");
  $uid = new Doc($action->GetParam("FREEDOM_DB"), $fid);
  $from = $uid->getValue("TITLE")." &lt;".getMailAddr($uid->getValue("US_WHATID"))."&gt;";
  $mailto = "";
  $attid = $event->getTValue("CALEV_ATTID", array()); 
  foreach ($attid as $k => $v) {
    if ($v != $action->user->fid ) {
      $u = new Doc($action->GetParam("FREEDOM_DB"), $v);
      $fullname = $u->getValue("TITLE");
      $mail = getMailAddr($u->getValue("US_WHATID"));
      $mailto .= ($mailto==""?"":", ").$fullname." &lt;".$mail."&gt;";
    }
  }
  if ($mailto != "") {

//     $m = new Layout( "WGCAL/Layout/mail_rv_".$action->GetParam("CORE_LANG", "fr_FR").".xml", $action );
    $m = new Layout( "WGCAL/Layout/mail_rv.xml", $action );
    $m->set("rvowner", $event->getValue("CALEV_OWNER"));
    $m->set("rvtitle", $event->getValue("CALEV_EVTITLE"));
    $m->set("dstart",  $event->getValue("CALEV_START"));
    $m->set("dend",  $event->getValue("CALEV_END"));
    $m->set("evid",  $event->id);
    $out = $m->gen();
    $tmpf = uniqid("/tmp/rv".$doc->id);
    $fout = fopen($tmpf,"w");
    fwrite($fout,$out);
    print_r2($out);
    fclose($fout);
    
    //$icsf = makeIcsFile($action, $event);

    $subject = "[Proposition de rendez-vous - Meeting proposal] ".$event->getValue("CALEV_EVTITLE");
    $cmd = "metasend  -b -S 4000000 -c '".$from."' -F '".$from."' -t '".$mailto."' -s \"".$subject."\"  ";
    $cmd .= " -m 'text/plain' -e 'quoted-printable' -i event -f '$tmpf' ";
    //$cmd .= " -m 'text/html' -e 'quoted-printable' -i ICS -f '$icsf' ";
    
    $cmd = "export LANG=C;".$cmd;
    echo "<pre>";
    echo "From : $from\n";
    echo "To : ".$mailto."\n";
    echo "Subject : $subject\n";
    echo "Command : $cmd\n";
    echo "</pre>";
    //system ($cmd, $status);
    @unlink($tmpf);
    //unlink($icsf);
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
