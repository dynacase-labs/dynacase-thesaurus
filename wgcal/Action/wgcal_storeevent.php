<?php

include_once("WHAT/Lib.Common.php");
include_once("FDL/Class.Doc.php");
include_once("FDL/mailcard.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_storeevent(&$action) {

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/DHTMLapi.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/PopupWindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");

  $db = $action->getParam("FREEDOM_DB");

  $checkForConflict = GetHttpVars("check", 1);

  $ds = GetHttpVars("Fstart", 0);
  $de = GetHttpVars("Fend", 0);
  $start = date2db($ds);
  $end = date2db($de);
  $htype = 0;
  if (GetHttpVars("nohour", "") == "on") {
    $htype = 1;
    $start = date2db($ds, false) . " 00:00";
    $end = date2db($de, false) . " 00:00";
  }
  if (GetHttpVars("allday", "") == "on") {
    $htype = 2;
    $start = date2db($ds, false)." 00:00";
    $end = date2db($ds, false)." 23:59";
  }
  $withme = GetHttpVars("withMe", "off");
  $attendees = array();
  $attl = GetHttpVars("attendees", "");
  if ($attl!="") $attendees = explode("|", $attl);
  if ($withme) $attendees[count($attendees)] = $action->user->fid;
  
  if ($checkForConflict) $conflict = CheckConflict($action, $attendees, $start, $end);
return;
  if ($conflict) return;



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
  
  
  $event->setValue("CALEV_TIMETYPE", $htype);
  $event->setValue("CALEV_START", $start);
  $event->setValue("CALEV_END", $end);
  
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
    foreach ($excludedate as $kd => $vd) if ($vd>0 && $vd!="") $tex[] = date2db($vd);
    $event->setValue("CALEV_EXCLUDEDATE", $tex);
  }
  

  // --------------------------------------------------------------------------------------------------
  // Attendees
  $udbaccess = $action->GetParam("COREUSER_DB");
  $ugrp = new User($udbaccess);
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $groupfid = getIdFromName($dbaccess, "GROUP");
  $igroupfid = getIdFromName($dbaccess, "IGROUP");

  $oldatt_id    = $event->getTValue("CALEV_ATTID", array());
  $oldatt_state = $event->getTValue("CALEV_ATTSTATE", array());
  $oldatt_group = $event->getTValue("CALEV_ATTGROUP", array());

  $nattl = array(); $iatt = 0;
  // first, find all groups and expand it
  foreach ($attendees as $ka => $va) {
    $att = new Doc($db, $va);
    if ($att->fromid==$groupfid || $att->fromid==$igroupfid) {
      $nattl[$iatt]["fid"] = $att->id;
      $nattl[$iatt]["fgid"] = -1;
      $iatt++;
      $ulist = $ugrp->GetUsersGroupList($att->getValue("US_WHATID"));
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
  
  $event->AddComment(_("change content "));
  $changed = true;
  //if ($changed) sendRv($action, $event);
  sendRv($action, $event);
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



function CheckConflict(&$action, $rl, $ds, $de) {
  $conflict = array();

  $nrl = array();
  foreach($rl as $k => $v) {
    $trl = GroupExplode($action, $v);
    $nrl = array_merge($nrl, $trl);
  }
//   echo "Recherche des conflits : ressource [$idres] debut [$ds] fin [$de]<br>";

  $tev = WGCalGetAgendaEvents($action, $nrl, $ds, $de);
  
  $action->lay->setBlockData("CARDS", $tev);

  foreach ($tev as $k=>$v) {
    $line[]["line"] = "[".$v["RG"]." id=".$v["ID"]." debut=".$v["TSSTART"]." fin=".$v["TSEND"]." owner=".$v["IDP"];
  }
  $action->lay->set("CF", (count($tev)>0 ? true : false));
  $action->lay->SetBlockData("CONFLICTS", $tev);
  return (count($tev)>0 ? true : false);
}

?>
