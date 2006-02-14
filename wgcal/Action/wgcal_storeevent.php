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
  $start = w_datets2db($ds).":00";
  $end = w_datets2db($de).":00";
  $htype = 0;
  if (GetHttpVars("nohour", "") == "on") {
    $htype = 1;
    $start = w_datets2db($ds, false) . " 00:00:00";
    $end = w_datets2db($ds, false) . " 00:00:00";
  }
  if (GetHttpVars("allday", "") == "on") {
    $htype = 2;
    $start = w_datets2db($ds, false)." 00:00:00";
    $end = w_datets2db($ds, false)." 23:59:59";
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
  if ($date>0) $sdate = $event->setValue("CALEV_REPEATUNTILDATE", w_datets2db($date, false)." 23:59:39");
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
  $convoc = GetHttpVars("evconvocation",0);
  $event->setValue("calev_convocation", $convoc);

  $event->setValue("calev_attextmail", GetHttpVars("evmailext",0));

  $withme = GetHttpVars("evwithme", 1);
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
	  if ($att->fromid==128) {
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
  if ($withme == 1) {
    $attendeesname[$attcnt] = $ownertitle;
    $attendeesid[$attcnt] = $owner;
    $attendeeswid[$attcnt] = $ownerwid;
    $attendeesstate[$attcnt] = ($convoc==1 ? -1 : $evstatus);
    $attendeesgroup[$attcnt] = -1;
  }
    
  $event->setValue("CALEV_ATTID", $attendeesid); 
  $event->setValue("CALEV_ATTWID", $attendeeswid); 
  $event->setValue("CALEV_ATTTITLE", $attendeesname); 
  $event->setValue("CALEV_ATTSTATE", $attendeesstate); 
  $event->setValue("CALEV_ATTGROUP", $attendeesgroup); 
    
  $err = $event->Modify();
  if ($err!="") AddWarningMsg(__FILE__."::".__LINE__."> $err");
  $err = $event->PostModify();
  if ($err!="") AddWarningMsg(__FILE__."::".__LINE__."> $err");
  

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

  if (is_array($oldrv)) {
    $newrv = $event->getValues();
    $change = rvDiff($oldrv, $newrv);
  }

  // 1) Creation => envoi d'un mail à tout les participants (sauf proprio)
  // 2) Modification de l'heure, répétition => envoi d'un mail à tout les participants et reset des acceptations
  // 3) Modification de l'acceptation => envoi d'un mail au proprio D'ICI CA VA ETRE DUR...
  // Modification du contenu => rien
  // Modification de la liste des participants => rien
  
  $mail_msg = $comment = "";
  $mail_who = -1;

  if ($newevent) {
    $mail_msg = _("event creation information message");
    $mail_who = 2;
    $comment = _("event creation");
  } else {
    if ($change["hours"]) {
      $mail_msg = _("event time modification message");
      $mail_who = 2;
      $comment = _("event modification time");
      $event->resetAcceptStatus();
    } else {
      if ($change["attendees"]) {
	$comment = _("event modification attendees list");
      } else {
	if ($change["status"]) {
	  $mail_msg = _("event acceptation status message");
	  $mail_who = 0;
	  $comment = _("event modification acceptation status");
	}
      }
    }
  }
  if ($comment!="") $event->AddComment($comment);
  if ($mail_who!=-1) {
    $title = $action->getParam("WGCAL_G_MARKFORMAIL", "[RDV]")." ".$event->getValue("calev_evtitle");
    sendRv($action, $event, $mail_who, $title, $mail_msg, true);
  }
  
  if ($action->user->fid!=$owner && $mail_who!=-1) {
    // Get Agenda delegation information : does the owner want to received mail ?
    $owneragenda = getUserAgenda($owner, true, "", false);
    if ($owneragenda[0]->isAffected() && $owneragenda[0]->getValue("agd_dmail")==1) {
      $title = "[Agenda $creatortitle] ".$event->getValue("calev_evtitle");
      sendRv($action, $event, 0, $title, "<i>"._("event set/change by")." ".$creatortitle."</i><br><br>".$mail_msg);
    }
  }

  $event->unlock(true);

//   Header("Location: ".$_SERVER["HTTP_REFERER"]);
   redirect($action, "WGCAL", "WGCAL_CALENDAR");
}


function rvDiff( $old, $new) {
  $diff = array();
  foreach ($old as $ko => $vo) {
    if (!isset($new[$ko])) {
      $diff[$ko] = "D";
    } else {
      if (strcmp($ko,"calev_start")==0 || strcmp($ko,"calev_end")==0) 
	{
	  if (strcmp(substr($vo, 0, 16), substr($new[$ko], 0, 16))!=0) $diff[$ko] = "M";
	}	
      else if ($vo!=$new[$ko]) $diff[$ko] = "M";
    }
  }
  foreach ($new as $ko => $vo) {
    if (!isset($new[$ko])) $diff[$ko] = "A";
  }

  $result = array( "content" => false, 
		   "hours" => false, 
		   "attendees" => false, 
		   "status" => false, 
		   "others" => false);


   foreach ($diff as $k => $v) {

    switch ($k) {
    case "calev_evtitle":      
    case "calev_evnote":
      $result["content"] = true;
      break;
    case "calev_start":
    case "calev_end":
    case "calev_timetype":
    case "calev_frequency":
    case "calev_repeatmode":
    case "calev_repeatweekday":
    case "calev_repeatmonth":
//     case "calev_repeatuntil":
    case "calev_repeatuntildate":
    case "calev_excludedate":
      $result["hours"] = true;
      break;
    case "calev_attid":
      $result["attendees"] = true;
      break;
    case "calev_attstate":
      $result["status"] = true;
      break;
    default:
      $result["others"] = true;
    }
  }
  return $result;
}
  
      
?>
