<?php

include_once("WHAT/Lib.Common.php");
include_once("FDL/Class.Doc.php");
include_once("FDL/mailcard.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_storeevent(&$action) {

  global $_SERVER;

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/DHTMLapi.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/PopupWindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $oldrv= false;
  $err = "";
  $newevent = false;
  $id  = GetHttpVars("eventid", -1);
  if ($id==-1 || $id=="") {
    $event = createDoc($dbaccess, "CALEVENT");
    $err = $event->Add();
    if ($err!="") AddWarningMsg(__FILE__."::".__LINE__.">$err");
    $newevent = true;
  } else {
    $event = new_Doc($dbaccess, $id);
    $oldrv = $event->getValues();
  }
  
  $owner = GetHttpVars("ownerid", -1);
  $down = getTDoc($dbaccess, $owner);
  $ownerwid = $down["us_whatid"];
  $ownertitle = GetHttpVars("ownertitle", "");

  $event->setValue("CALEV_OWNERID", $owner);
  $event->setValue("CALEV_OWNER", $ownertitle);

  $creatorid = GetHttpVars("creatorid", -1); 
  if ($owner==$creatorid) {
    $creatorid = $owner;
    $creatorwid = $ownerwid;
    $creatortitle = $ownertitle;
  } else {
    $dcrea = getTDoc($dbaccess, $creatorid);
    $creatorwid = $dcrea["us_whatid"];
    $creatortitle = $dcrea["title"];
  }
  $event->setValue("calev_creatorid", $creatorid);
  $event->setValue("calev_creator", $creatortitle);

  $evstatus = GetHttpVars("evstatus", EVST_NEW);

  $event->setValue("CALEV_EVTITLE", GetHttpVars("rvtitle"));
  $event->setValue("CALEV_EVNOTE", GetHttpVars("rvnote", ""));
  $event->setValue("CALEV_CATEGORY", GetHttpVars("evcategory", 0));
  
  $ds = (GetHttpVars("TsStart", 0)/1000);
  $de = (GetHttpVars("TsEnd", 0)/1000);
  $start = date("d/m/Y H:i:00",$ds);
  $end = date("d/m/Y H:i:00",$de);
  $htype = 0;
  if (GetHttpVars("nohour", "") == "on") {
    $htype = 1;
    $start =date("d/m/Y 00:00:00",$ds);
    $end = date("d/m/Y 00:00:00",$ds);
  }
  if (GetHttpVars("allday", "") == "on") {
    $htype = 2;
    $start =date("d/m/Y 00:00:00",$ds);
    $end = date("d/m/Y 23:59:59",$ds); 
  }
  if (!$newevent) {
    $ott = $event->getValue("CALEV_TIMETYPE"); 
    $ott = ($ott==""?0:$ott);
    $ostart = $event->getValue("CALEV_START");
    $oend = $event->getValue("CALEV_END");
  }
  $event->setValue("CALEV_TIMETYPE", $htype);
  $event->setValue("CALEV_START", $start);
  $event->setValue("CALEV_END", $end);
  
  $event->setValue("CALEV_FREQUENCY", GetHttpVars("rfrequency",1));
  
  $event->setValue("calev_location", GetHttpVars("rvlocation",""));

  $calid = GetHttpVars("evcalendar", -1);
  $caltitle = _("main calendar");
  if (!$newevent) $oldcal = $event->getValue("CALEV_EVCALENDARID");
  else $oldcal = -1;
  $event->setValue("CALEV_EVCALENDARID", $calid);
  if ($calid != -1) {
    $cal = new_Doc($dbaccess, $calid);
    $caltitle = $cal->title;
  }
  $event->setValue("CALEV_EVCALENDAR", $caltitle);

  $conf = GetHttpVars("evconfidentiality", 0);
  $event->setValue("CALEV_VISIBILITY", $conf);
  $event->confidential = ($conf>0 ? "1" : "0");

  $confg = GetHttpVars("evconfgroups", 0);
  $event->setValue("CALEV_CONFGROUPS", $confg);
  
  $event->setValue("calev_evalarm", GetHttpVars("evalarmst",0));
  $event->setValue("calev_evalarmday", GetHttpVars("evalarmd",0));
  $event->setValue("calev_evalarmhour", GetHttpVars("evalarmh",1));
  $event->setValue("calev_evalarmmin", GetHttpVars("evalarmm",0));
  
  // repeat 
  $rmode = GetHttpVars("repeattype", 0);
  $event->setValue("CALEV_REPEATMODE", $rmode);

  $rweekday = GetHttpVars("evrweekday", 0);
  $daymask = 0;
  if ($rmode==2) {
    foreach ($rweekday as $k => $v) $daymask = $daymask | pow(2,$v);
  }
  $event->setValue("CALEV_REPEATWEEKDAY", $daymask);

  $event->setValue("CALEV_REPEATMONTH", GetHttpVars("rmonth", 0));
  $event->setValue("CALEV_REPEATUNTIL", GetHttpVars("runtil", 0));
  $date = GetHttpVars("evruntildate");
  if ($date>0) $sdate = $event->setValue("CALEV_REPEATUNTILDATE", date("d/m/Y 23:59:00",$date));
  $excl = GetHttpVars("excludedate", "");
  $event->deleteValue("CALEV_EXCLUDEDATE");
  if ($excl != "") {
    $excludedate = explode("|",$excl);
    foreach ($excludedate as $kd => $vd) if ($vd>0 && $vd!="") $tex[] = w_datets2db($vd);
    $event->setValue("CALEV_EXCLUDEDATE", $tex);
  }
  

  // --------------------------------------------------------------------------------------------------
  // Attendees
  // --------------------------------------------------------------------------------------------------
  $oconvoc = $event->getValue("calev_convocation");
  $convoc = GetHttpVars("evconvocation",0); 
  $event->setValue("calev_convocation", $convoc);

  $event->setValue("calev_attextmail", GetHttpVars("evmailext",0));

  $udbaccess = $action->GetParam("COREUSER_DB");
  $ugrp = new User($udbaccess);
  $groupfid = getIdFromName($dbaccess, "GROUP");
  $igroupfid = getIdFromName($dbaccess, "IGROUP");

  $oldatt_id    = $event->getTValue("CALEV_ATTID", array());
  $oldatt_wid   = $event->getTValue("CALEV_ATTWID", array());
  $oldatt_state = $event->getTValue("CALEV_ATTSTATE", array());
  $oldatt_group = $event->getTValue("CALEV_ATTGROUP", array());

  $attendeesid    = array();
  $attendeeswid   = array();
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
      $att = new_Doc($dbaccess, $va);
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
      $att = new_Doc($dbaccess, $va);
      if ($att->fromid!=$groupfid && $att->fromid!=$igroupfid) {
	$nattl[$iatt]["fid"] = $att->id;
	$nattl[$iatt]["fgid"] = -1;
	$iatt++;
      }
    }
  
    foreach ($nattl as $ka => $va) {
      if ($va<=0||$va=="") continue;
      if ($va["fid"] != $owner) {
	$att = new_Doc($dbaccess, $va["fid"]);
	$attendeesid[$attcnt]  = $att->id;
	$attendeeswid[$attcnt]  = $att->getValue("us_whatid");
	$attendeesname[$attcnt]  = $att->getTitle();
	$attendeesgroup[$attcnt] = $va["fgid"];
	if ($convoc==1) {
	  $attendeesstate[$attcnt] = -1;
	} else {
	  if (wIsFamilieInteractive($att->fromid)) {
	    $attendeesstate[$attcnt] = 0;
	    foreach ($oldatt_id as $ko => $vo) {
	      if ($vo == $va["fid"]) $attendeesstate[$attcnt] = $oldatt_state[$ko];
	    }
	  } else {
	    $attendeesstate[$attcnt] = -1;
	  }
	}
     }
      $attcnt++;
    }
  }
  $attendeesname[$attcnt] = $ownertitle;
  $attendeesid[$attcnt] = $owner;
  $attendeeswid[$attcnt] = $ownerwid;
  $attendeesstate[$attcnt] = ($convoc==1 ? -1 : $evstatus);
  $attendeesgroup[$attcnt] = -1;
    
  $event->setValue("CALEV_ATTID", $attendeesid); 
  $event->setValue("CALEV_ATTWID", $attendeeswid); 
  $event->setValue("CALEV_ATTTITLE", $attendeesname); 
  $event->setValue("CALEV_ATTSTATE", $attendeesstate); 
  $event->setValue("CALEV_ATTGROUP", $attendeesgroup); 

  $err = $event->Modify();
  if ($err!="") AddWarningMsg(__FILE__."::".__LINE__."> $err");
  $err = $event->PostModify();
  if ($err!="") AddWarningMsg(__FILE__."::".__LINE__."> $err");
  
  if (!$newevent && $oconvoc==1 && $convoc==0)  $event->ResetAttendeesStatus();
    
  $event->setAccessibility();


  // Gestion du calendrier d'appartenance
  if ($oldcal != $calid && !$newevent) {
    if ($oldcal!=-1) {
      $cal = new_Doc($dbaccess, $oldcal);
      $cal->DelFile($event->id);
      $event->AddComment(_("remove event from calendar")." ".$cal->title);
    }
    $cal = new_Doc($dbaccess, $calid);
    if ($cal->isAlive()) {
      $cal->AddFile($event->id);
      $event->AddComment(_("insert event in calendar")." ".$caltitle);
    }
  }

  $event->unlock(true);


  $event->postChangeProcess($oldrv);

  redirect($action, "WGCAL", "WGCAL_CALENDAR");
}


      
?>
