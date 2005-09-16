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
  
  $ds = GetHttpVars("Fstart", 0);
  $de = GetHttpVars("Fend", 0);
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
  
  $event->setValue("CALEV_FREQUENCY", GetHttpVars("frequency",1));
  
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
  $event->setValue("confidential", ($conf>0 ? "1" : "0"));

  $confg = GetHttpVars("evconfgroups", 0);
  $event->setValue("CALEV_CONFGROUPS", $confg);
  
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
  if ($date>0) $sdate = $event->setValue("CALEV_REPEATUNTILDATE", w_datets2db($date));
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
  $attendeeswid    = array();
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
	if ($att->fromid==128) {
	  $attendeesstate[$attcnt] = 0;
	  foreach ($oldatt_id as $ko => $vo) {
	    if ($vo == $va["fid"]) $attendeesstate[$attcnt] = $oldatt_state[$ko];
	  }
	} else {
	  $attendeesstate[$attcnt] = -1;
	}
     }
      $attcnt++;
    }
  }
  if ($withme == 1) {
    $attendeesname[$attcnt] = $ownertitle;
    $attendeesid[$attcnt] = $owner;
    $attendeeswid[$attcnt] = $ownerwid;
    $attendeesstate[$attcnt] = $evstatus;
    $attendeesgroup[$attcnt] = -1;
  }
    
  $event->setValue("CALEV_ATTID", $attendeesid); 
  $event->setValue("CALEV_ATTWID", $attendeeswid); 
  $event->setValue("CALEV_ATTTITLE", $attendeesname); 
  $event->setValue("CALEV_ATTSTATE", $attendeesstate); 
  $event->setValue("CALEV_ATTGROUP", $attendeesgroup); 
    
  $err = $event->Modify();
  if ($err!="") AddWarningMsg(__FILE__."::".__LINE__."> $err");
  else {
    $err = $event->PostModify();
    if ($err!="") AddWarningMsg(__FILE__."::".__LINE__."> $err");
  }
  
  $event->SetControl();
  $event->profid = $event->id;
  $err = $event->Modify();
  if ($err!="") AddWarningMsg(__FILE__."::".__LINE__."> $err");
  

  // [init] => 0 (control initialized)
  // [view] => 1 (view document)
  // [send] => 4 (send document)
  // [edit] => 2 (edit document)
  // [delete] => 3 (delete document)
  // [open] => 5 (open folder)
  // [execute] => 5 (execute search)
  // [modify] => 6 (modify folder)
  // [viewacl] => 7 (view acl)
  // [modifyacl] => 8 (modify acl)
  // [create] => 5 (create doc)
  // [unlock] => 9 (unlock unowner locked doc)
  // [icreate] => 6 (create doc manually)
  // [confidential]   define("R_READ",0);
  
  $userf = new_Doc($dbaccess, $owner);
  $acl = array();
  
  $sdeb = "Confidential=".$event->getValue("confidential")."\nRV Confidentiality=[$conf]\nAgenda visibility:".($userf->getValue("us_wgcal_vcalgrpmode")==1?"Groups":"Public")."\n";


  // User agenda visibility
  $vcalrestrict = false;
  $vcal = array();
  $calgvis = $userf->getValue("us_wgcal_vcalgrpmode");
  if ($calgvis == 1) {
    $vcalrestrict = true;
    $tvcal = $userf->getTValue("us_wgcal_vcalgrpwid");
    foreach ($tvcal as $k => $v) $vcal[$v] = $v; 
  } else {
    $vcal[2] = 2;
  }
  
  $tcfg = explode("|", $confg);
  $rvcgroup = array();
  foreach ($tcfg as $k => $v) {
    if ($v!="") $rvcgroup[$v] = $v;
  }


  // foreach attendees (except owner) get agenda groups
  $attgrps = array();
  foreach ($attendeesid as $k => $v) {
    if ($attendeesstate[$k]!=-1) {
      $ugrp = wGetUserGroups($v);
      if (count($ugrp)>0) {
	foreach ($ugrp as $kg => $vg) {
	  $thisg = getTDoc($dbaccess, $kg);
	  if (!isset($rvcgroup[$thisg["us_whatid"]])) {
	    $attgrps[$thisg["us_whatid"]] = $thisg["us_whatid"];
	  }
	}
      }
    }
  }

  
  $aclv = array();

  $aclv["all"] = array( "view"=>"view", 
			"confidential"=>"confidential", 
			"send"=>"send", 
			"edit"=>"edit", 
			"delete"=>"delete", 
			"execute"=>"execute", 
			"unlock"=>"unlock",
			"viewacl"=>"viewacl", 
			"modifyacl"=>"modifyacl");
  $aclv["none"] = array();
  $aclv["edit"] = array( "edit"=>"edit", 
			 "unlock"=>"unlock",
			 "execute"=>"execute", 
			 "viewacl"=>"viewacl", 
			 "modifyacl"=>"modifyacl",
			 "confidential"=>"confidential",
			 "view"=>"view" );
  $aclv["read"] = array( "view"=>"view");
  $aclv["read_state"] = array( "view"=>"view", 
			       "execute"=>"execute");
  $aclv["read_conf"] = array( "view"=>"view", 
			      "confidential"=>"confidential");
  $aclv["read_conf_state"] = array( "view"=>"view", 
				    "confidential"=>"confidential", 
				    "execute"=>"execute");

  
  $aclvals = array();
  foreach ($aclv as $ka => $va) {
    foreach ($aclv["all"] as $kr => $vr) {
      if (isset($va[$kr])) $aclvals[$ka][$kr] = $event->dacls[$kr]["pos"];
      else $aclvals[$ka][$kr] = 0;
    }
  }


  $acls = array();

  // Attendees -> read, confidential and execute at least
  foreach ($attendeeswid as $k => $v) {
    if ($attendeesstate[$k]!=-1) {
      if ($v!=$ownerwid && $v!=$creatorwid) $acls[$v] = $aclvals["read_conf_state"];
    }
  }

  // Owner, creator and delegate ==> owner rights
  $calownermode = array();
  $acls[$ownerwid] = $aclvals["all"];
  if ($creatorid!=$owner) $acls[$creatorwid] = $aclvals["all"];
  $duid = $userf->getTValue("us_wgcal_dguid");
  $duwid = $userf->getTValue("us_wgcal_dguwid");
  $dumode = $userf->getTValue("us_wgcal_dgumode");
  foreach ($duid as $k=>$v) {
    if ($dumode[$k] == 1) $acls[$duwid[$k]] = $aclvals["all"];
  }

  switch ($conf) {
  case 1: // Private
    foreach ($vcal as $k => $v) $acls[$k] = $aclvals["read"] ;
    foreach ($attgrps as $k => $v) $acls[$k] = $aclvals["read"];
    if ($calgvis==0) $acls[2] = $aclvals["read"];
    else $acls[2] = $aclvals["none"];
    break;
    
  case 2: // My groups
    foreach ($vcal as $k => $v) $acls[$k] = $aclvals["read"];
    foreach ($rvcgroup as $k => $v) $acls[$k] = $aclvals["read_conf"];
    foreach ($attgrps as $k => $v) $acls[$k] = $aclvals["read"];
    $acls[2] = $aclvals["read"];
    break;
    
  default: // Public
    foreach ($vcal as $k => $v) $acls[$k] =  $aclvals["read"];
    foreach ($attgrps as $k => $v) {
      if ($calgvis==0)  $acls[$k] = $aclvals["read"];
      else $acls[$k] = $aclvals["read"];
    }
    if ($calgvis==0)     $acls[2] = $aclvals["read"];
    else $acls[2] = $aclvals["none"];
  }
  
  foreach ($acls as $user => $uacl) {
    
    $dt = getDocFromUserId($dbaccess,$user);
    $sdeb .= "[".$dt->GetTitle()."($user)] = ";

    $perm = new DocPerm($dbaccess, array($event->id,$user));

    $perm->UnsetControl();
    foreach ($uacl as $k => $v) {
      $sdeb .= "$k:$v ";
      if (intval($v) > 0)  {
	$perm->SetControlP($v);
      } else {
	$perm->SetControlN($v);
      }	
    }
    if ($perm->isAffected()) $perm->modify();
    else $perm->Add();
    $sdeb .= "\n";
  }
  
//    AddWarningMsg(  $sdeb );
  

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
      resetAcceptStatus($event);
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
    $title = "[Agenda $creatortitle] ".$event->getValue("calev_evtitle");
    sendRv($action, $event, 0, $title, _("event set/change by")." ".$creatortitle." -- ".$mail_msg);
  }

  $event->unlock(true);

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

//   $sdb = "Modifié : \n";

   foreach ($diff as $k => $v) {

//      $sdb .= "- $k [$v] ";
//      switch($v) {
//      case "D":
//        $sdb .= " old=(".$old[$k].") new=()";
//        break;
//      case "M":
//        $sdb .= " old=(".$old[$k].") new=(".$new[$k].")";
//        break;
//      case "A":
//        $sdb .= " old=() new=(".$new[$k].")";
//        break;
//      }
//      $sdb .= "\n";
		
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
//    AddWarningMsg(  $sdb );
  return $result;
}
  
function resetAcceptStatus(&$event) {
  global $action;
  $att_ids = $event->getTValue("CALEV_ATTID");
  if (count($att_ids)>0) {
    $att_wid = $event->getTValue("CALEV_ATTWID");
    $att_sta = $event->getTValue("CALEV_ATTSTATE");
    $att_grp = $event->getTValue("CALEV_ATTGROUP");
    foreach ($att_ids as $k => $v) {
      if ($att_grp[$k]==-1) {
	if ($v == $event->getValue("calev_ownerid")) $att_sta[$k] = EVST_ACCEPT;
	else {
	  if ($att_sta[$k] != -1) $att_sta[$k] = EVST_NEW;
	}
      }
    }
    $event->setValue("CALEV_ATTSTATE", $att_sta);
    $err = $event->Modify();
    if ($err=="") $err = $event->PostModify();
    if ($err!="") AddWarningMsg(__FILE__."::".__LINE__.">$err");
  }
}
      
?>
