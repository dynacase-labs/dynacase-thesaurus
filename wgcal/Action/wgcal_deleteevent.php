<?php

include_once("FDL/Class.Doc.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_deleteevent(&$action) {

  $db = $action->getParam("FREEDOM_DB");
  $ev     = GetHttpVars("ev", -1);
  if ($ev==-1) $evid = -1;
  else {
    $evtmp = new Doc($db, $ev);
    $evid = $evtmp->getValue("evt_idinitiator");
  }
  if ($evid<1) return;
  
  $event = new Doc($db, $evid);
  if ($event->isAlive()) {
    $err = $event->Delete();
    if ($err!="") AddWarningMsg("$err");
    $err = $event->postDelete();
    if ($err!="") AddWarningMsg("$err");
  }
  sendRv($action, $event);
//   $event->AddComment(_("event delete "));
  redirect($action, $action->parent->name, "WGCAL_CALENDAR");
}
?>