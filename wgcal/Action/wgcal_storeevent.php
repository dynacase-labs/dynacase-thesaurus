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

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $err = "";
  $newevent = false;
  $comment = "";
  $id  = GetHttpVars("eventid", -1);
  if ($id==-1) {
    $event = createDoc($dbaccess, "CALEVENT");
    $newevent = true;
    $comment = _("event creation");
  } else {
    $event = new Doc($dbaccess, $id);
  }
  
  $owner = GetHttpVars("ownerid", -1);
  $ownertitle = GetHttpVars("ownertitle", "");
  $event->setValue("CALEV_OWNERID", $owner);
  $event->setValue("CALEV_OWNER", $ownertitle);

  $evstatus = GetHttpVars("evstatus", EVST_NEW);

  $event->setValue("CALEV_EVTITLE", GetHttpVars("rvtitle"));
  $event->setValue("CALEV_EVNOTE", GetHttpVars("rvnote", ""));
  
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
  $start .= ":00";
  $end .= ":00";
  if (!$newevent) {
    $ott = $event->getValue("CALEV_TIMETYPE"); 
    $ott = ($ott==""?0:$ott);
    $ostart = $event->getValue("CALEV_START");
    $oend = $event->getValue("CALEV_END");
//     echo "old type = [".$ott."] new type = [".$htype."]<br>";
//     echo "old start = [".$ostart."] new start = [".$start."]<br>";
//     echo "old end = [".$oend."] new end = [".$end."]<br>";
    if ($ott!=$htype || $ostart!=$start || $oend!=$end) $comment = _("date modification");
  }
  $event->setValue("CALEV_TIMETYPE", $htype);
  $event->setValue("CALEV_START", $start);
  $event->setValue("CALEV_END", $end);
  
  $event->setValue("CALEV_FREQUENCY", GetHttpVars("frequency",1));
  
  $calid = GetHttpVars("evcalendar", -1);
  $caltitle = _("main calendar");
  if (!$newevent) $oldcal = $event->getValue("CALEV_EVCALENDARID");
  else $oldcal = -1;
  $event->setValue("CALEV_EVCALENDARID", $calid);
  if ($calid != -1) {
    $cal = new Doc($dbaccess, $calid);
    $caltitle = $cal->title;
  }
  $event->setValue("CALEV_EVCALENDAR", $caltitle);

  $event->setValue("CALEV_VISIBILITY", GetHttpVars("evconfidentiality", 0));
  if (GetHttpVars("evconfidentiality", 0)==1) $event->setprofil(getIdFromName("RV_PRIVATE"));
  
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
  $event->deleteValue("CALEV_EXCLUDEDATE");
  if ($excl != "") {
    $excludedate = explode("|",$excl);
    foreach ($excludedate as $kd => $vd) if ($vd>0 && $vd!="") $tex[] = date2db($vd);
    $event->setValue("CALEV_EXCLUDEDATE", $tex);
  }
  

  // --------------------------------------------------------------------------------------------------
  // Attendees
  $udbaccess = $action->GetParam("COREUSER_DB");
  $ugrp = new User($udbaccess);
  $groupfid = getIdFromName($dbaccess, "GROUP");
  $igroupfid = getIdFromName($dbaccess, "IGROUP");

  $oldatt_id    = $event->getTValue("CALEV_ATTID", array());
  $oldatt_state = $event->getTValue("CALEV_ATTSTATE", array());
  $oldatt_group = $event->getTValue("CALEV_ATTGROUP", array());

  $attendeesid    = array();
  $attendeesname  = array();
  $attendeesstate = array();
  $attendeesgroup = array();
  $attcnt = 0;
  $nattl = array(); $iatt = 0;
  // first, find all groups and expand it
  $attl = GetHttpVars("attendees", "");
  if ($attl!="") {
    $attendees = explode("|", $attl);
    foreach ($attendees as $ka => $va) {
      $att = new Doc($dbaccess, $va);
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
      $att = new Doc($dbaccess, $va);
      if ($att->fromid!=$groupfid && $att->fromid!=$igroupfid) {
	$nattl[$iatt]["fid"] = $att->id;
	$nattl[$iatt]["fgid"] = -1;
	$iatt++;
      }
    }
    //     print_r2($nattl);
  
   foreach ($nattl as $ka => $va) {
      if ($va<=0||$va=="") continue;
      $att = new Doc($dbaccess, $va["fid"]);
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
  
  $withme = GetHttpVars("withMe", "off");
  if ($withme == "on" && $owner==$action->user->fid) {
    $attendeesname[$attcnt] = $action->user->lastname." ".$action->user->firstname;
    $attendeesid[$attcnt] = $action->user->fid;
    $attendeesstate[$attcnt] = $evstatus;
    $attendeesgroup[$attcnt] = -1;
  }
    
  $event->setValue("CALEV_ATTID", $attendeesid); 
  $event->setValue("CALEV_ATTTITLE", $attendeesname); 
  $event->setValue("CALEV_ATTSTATE", $attendeesstate); 
  $event->setValue("CALEV_ATTGROUP", $attendeesgroup); 
    

  if (!$event->IsAffected()) $err = $event->Add();
  if ($err!="") {
     AddWarningMsg("$err");
  } else {
     $err = $event->Modify();
     if ($err!="") {
        AddWarningMsg("$err");
     } else {
        $err = $event->PostModify();
        if ($err!="") AddWarningMsg("$err");
     }
  }
  if ($oldcal != $calid && !$newevent) {
    if ($oldcal!=-1) {
      $cal = new Doc($dbaccess, $oldcal);
      $cal->DelFile($event->id);
      $event->AddComment(_("remove event from calendar ".$cal->title));
    }
    $cal = new Doc($dbaccess, $calid);
    if ($cal->isAlive()) {
      $cal->AddFile($event->id);
      $event->AddComment(_("insert event in calendar $caltitle"));
    }
  }
  $event->AddComment(($comment==""?_("change content "):$comment));
  $changed = true;

  sendRv($action, $event, 1, _("event content modification information message"));
  redirect($action, "WGCAL", "WGCAL_CALENDAR");
}

function wgcal_getArrayVal($key, $def=null) {
  $v = $def;
  $a = GetHttpVars($key, array());
  if (count($a)>0) $v = $a[0];
  return $v;
}



?>
