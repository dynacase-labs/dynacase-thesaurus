<?php

include_once("WHAT/Lib.Common.php");
include_once("FDL/Class.Doc.php");
include_once("FDL/mailcard.php");
include_once("WGCAL/Lib.Agenda.php");

function wgcal_saveevent(&$action) {
  
  $dbaccess = getParam("FREEDOM_DB");

  $oid   = GetHttpVars("oid", $action->user->fid);   // Owner Freedom Id
  $idp   = GetHttpVars("ip", -1);                    // Prod. Id (if exists)
  $title = GetHttpVars("ti", "");                    // Title
  $nh    = GetHttpVars("nh", 0);                     // 0 : ts & te 1:no time 2:all day
  $ts    = GetHttpVars("ts", 0);                     // Time start
  $te    = GetHttpVars("te", 0);                     // Time end
  $lo    = GetHttpVars("lo", "");                    // Location
  $no    = GetHttpVars("no", "");                    // Note
  $cat   = GetHttpVars("cat", "0");                  // Categorie
  echo "nh=$nh<br>";
  if ($idp==-1 || $title=="" || $ts==0 || $te==0) {
    return;
  }

  if ($idp==0) {
    $event = createDoc($dbaccess, "CALEVENT");
    $err = $event->Add();
  } else {
    $event = new_Doc($dbaccess, $idp);
  }

  $down = new_Doc($dbaccess, $oid);
  $dcre = new_Doc($dbaccess, $action->user->fid);
  $event->setValue("calev_ownerid", $oid);
  $event->setValue("calev_owner", $down->getValue("title"));

  $event->setValue("calev_creatorid", $action->user->fid);
  $event->setValue("calev_creator",$dcre->getValue("title"));

  $event->setValue("calev_evtitle", $title);
  $event->setValue("calev_evnote", $no);
  $event->setValue("calev_category", $cat);
  $event->setValue("calev_location", $lo);

  if ($nh==1) {
    $event->setValue("calev_start", gmdate("d/m/Y 00:00:00",$ts));
    $event->setValue("calev_end", gmdate("d/m/Y 00:00:00",$te));
    $event->setValue("calev_timetype", 1);
  } else if ($nh==21) {
    $event->setValue("calev_start", gmdate("d/m/Y 00:00:00",$ts));
    $event->setValue("calev_end", gmdate("d/m/Y 23:59:00",$te));
    $event->setValue("calev_timetype", 2);
  } else {
    $event->setValue("calev_start", gmdate("d/m/Y H:i:00",$ts));
    $event->setValue("calev_end", gmdate("d/m/Y H:i:00",$te));
    $event->setValue("calev_timetype", 0);
  }
    
  $event->setValue("calev_frequency", 1);

  $cal = getUserPublicAgenda();
  $event->setValue("calev_evcalendarid", -1); //$cal["id"] );
  $event->setValue("calev_evcalendar", $cal["title"] );
  
  $event->confidential = 0;
  $event->setValue("calev_visibility", 0);

  $event->setValue("calev_confgroups", 0);

  $event->setValue("calev_evalarm", 0);
  $event->setValue("calev_evalarmday", 0);
  $event->setValue("calev_evalarmhour", 0);
  $event->setValue("calev_evalarmmin", 0);

  $event->setValue("calev_repeatmode", 0);

  $event->setValue("calev_convocation", 0);

  $event->setValue("calev_attid", array($oid));
  $event->setValue("calev_attwid", array($down->getValue("us_whatid")));
  $event->setValue("calev_attstate", array(2));
  $event->setValue("calev_attgroup", array(-1));

  $err = $event->Modify();
//   echo "Modify err=$err<br>";
  $err = $event->PostModify();
//   echo "PostModify err=$err<br>";

  $event->setAccessibility();
  $event->unlock(true);

  $action->lay->set("OUT", $event->id);
  return ;
}
?>
  
 