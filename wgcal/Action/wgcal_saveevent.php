<?php

include_once("WHAT/Lib.Common.php");
include_once("FDL/Class.Doc.php");
include_once("FDL/mailcard.php");
include_once("WGCAL/Lib.Agenda.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_saveevent(&$action) {

  $dbaccess = getParam("FREEDOM_DB");

  $idp   = GetHttpVars("id", 0);                    // Prod. Id (if exists)
  $oid   = GetHttpVars("oi", $action->user->fid);    // Owner Freedom Id
  $title = GetHttpVars("ti", "");                    // Title
  $nh    = GetHttpVars("nh", 0);                     // 0 : ts & te 1:no time 2:all day
  $ts    = GetHttpVars("ts", time());                     // Time start
  $te    = GetHttpVars("te", time()+3600);                     // Time end
  $lo    = GetHttpVars("lo", "");                    // Location
  $no    = GetHttpVars("no", "");                    // Note
  $cat   = GetHttpVars("ca", "0");                   // Categorie
  $conf  = GetHttpVars("co", "0");                   // Categorie
  $alarm = GetHttpVars("alrm", 0);                   // Categorie

  $action->lay->set("showevent", false);

  if ($title=="" || $ts==0 || $te==0) {
    $action->lay->set("status", -1);
    $action->lay->set("statustext", _("internal server error ").basename(__FILE__)."::".__LINE__);
    return;
  }

  $oldrv= false;
  if ($idp==0) {
    $event = createDoc($dbaccess, "CALEVENT");
    $err = $event->Add();
    $new = true;
  } else {
    $event = new_Doc($dbaccess, $idp);
    $new = false;
    $oldrv = $event->getValues();
  }

  $down = new_Doc($dbaccess, $oid);
  $dcre = new_Doc($dbaccess, $action->user->fid);

  if ($new) {
    $event->setValue("calev_ownerid", $oid);
    $event->setValue("calev_owner", $down->getValue("title"));
  }

  $event->setValue("calev_creatorid", $action->user->fid);
  $event->setValue("calev_creator",$dcre->getValue("title"));

  $event->setValue("calev_evtitle", $title);
  $event->setValue("calev_evnote", $no);
  $event->setValue("calev_category", $cat);
  $event->setValue("calev_evalarm", $alarm);
  $event->setValue("calev_location", $lo);

  if ($nh==1) {
    $event->setValue("calev_start", date("d/m/Y 00:00:00",$ts));
    $event->setValue("calev_end", date("d/m/Y 00:00:00",$te));
    $event->setValue("calev_timetype", 1);
  } else if ($nh==2) {
    $event->setValue("calev_start", date("d/m/Y 00:00:00",$ts));
    $event->setValue("calev_end", date("d/m/Y 23:59:00",$te));
    $event->setValue("calev_timetype", 2);
  } else {
    $event->setValue("calev_start", date("d/m/Y H:i:00",$ts));
    $event->setValue("calev_end", date("d/m/Y H:i:00",$te));
    $event->setValue("calev_timetype", 0);
  }
    
  if ($new) {
    $event->setValue("calev_frequency", 1);
    $cal = getUserPublicAgenda();
    $event->setValue("calev_evcalendarid", -1); //$cal["id"] );
    $event->setValue("calev_evcalendar", $cal["title"] );
  }

  $event->confidential = ($conf>0 ? 1 : 0);
  $event->setValue("calev_visibility", $conf);
  $event->setValue("calev_confgroups", 0);
  if ($conf==2) {
    $lgroups = array();
    $u_groups = wGetUserGroups($oid);
    if (count($u_groups)>0) {
      foreach ($u_groups as $k => $v) {
	$dt = getTDoc($dbaccess, $k);
	$lgroups[] = getV($dt, "us_whatid");
      }
      $event->setValue("calev_confgroups", implode("|", $lgroups));
    } else { // Error, no groups, I set privacy to confidentiel
      $event->setValue("calev_visibility", 1);
    }
  }

  if ($new) {
    $event->setValue("calev_repeatmode", 0);
    $event->setValue("calev_convocation", 0);
    $event->setValue("calev_attid", array($oid));
    $event->setValue("calev_attwid", array($down->getValue("us_whatid")));
    $event->setValue("calev_attstate", array(2));
    $event->setValue("calev_attgroup", array(-1));
  }

  $err = $event->Modify();
  if ($err!="") {
    $action->lay->set("status", -1);
    $action->lay->set("statustext", "Freedom internal error doc->modify(): $err");
    return;
  } 

  $err = $event->PostModify();
  if ($err!="") {
    $action->lay->set("status", -1);
    $action->lay->set("statustext", "Freedom internal error doc->PostModify(): $err");
    return;
  } 

//   $event->setAccessibility();
  $event->unlock(true);

  $event->postChangeProcess($oldrv);

  return ;
}
?>
  
 