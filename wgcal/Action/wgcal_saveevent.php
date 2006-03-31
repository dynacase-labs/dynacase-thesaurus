<?php

include_once("WHAT/Lib.Common.php");
include_once("FDL/Class.Doc.php");
include_once("FDL/mailcard.php");
include_once("WGCAL/Lib.Agenda.php");

function wgcal_saveevent(&$action) {
  
  $dbaccess = getParam("FREEDOM_DB");

  $idp = GetHttpVars("idp", -1);
  $title = GetHttpVars("title", "");
  $ts = GetHttpVars("ts", 0);
  $te = GetHttpVars("te", 0);

  print "idp=[$idp] title=[$title] ts=[$ts] te=[$te]"; 
  if ($idp==-1 || $title="" || $ts==0 || $te==0) {
    print "idp=[$idp] title=[$title] ts=[$ts] te=[$te]"; 
    return;
  }

  if ($idp==0) {
    $event = createDoc($dbaccess, "CALEVENT");
    $err = $event->Add();
  } else {
    $event = new_Doc($dbaccess, $idp);
  }

  $event->setValue("calev_ownerid", $action->user->fid);
  $down = getTDoc($dbaccess, $owner);
  $event->setValue("calev_owner", $down["title"]);
  $event->setValue("calev_creatorid", $action->user->fid);
  $event->setValue("calev_creator", $down["title"]);

  $event->setValue("calev_evtitle", $title);
  $event->setValue("calev_evnote", "");
  $event->setValue("calev_category", 0);

  $event->setValue("calev_start", date("d/m/Y H:i:00",$ts));
  $event->setValue("calev_end", date("d/m/Y H:i:00",$te));
  $event->setValue("calev_timetype", 0);

  $event->setValue("calev_frequency", 1);

  $cal = getUserPublicAgenda();
  $event->setValue("calev_evcalendarid", $cal["id"] );
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

  $event->setValue("calev_attid", array());
  $event->setValue("calev_attwid", array());
  $event->setValue("calev_attstate", array());
  $event->setValue("calev_attgroup", array());

  $err = $event->Modify();
  echo "Modify err=$err<br>";
  $err = $event->PostModify();
  echo "PostModify err=$err<br>";

  $event->setAccessibility();
  $event->unlock(true);

  print_r2($event->getValues());
  $action->lay->set("OUT", $event->id);
  return ;
}
?>
  
 