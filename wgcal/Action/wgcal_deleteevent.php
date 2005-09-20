<?php

include_once("FDL/Class.Doc.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_deleteevent(&$action, $optev=-1) {

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $evi = GetHttpVars("id", -1);
  $event = new_Doc($dbaccess, $evi);
  if (!$event->isAffected()) {
    AddWarningMsg("No event # $evi $cev ");
  } else {
    if ($event->isAlive()) {
      $err = $event->Delete();
      if ($err!="") AddWarningMsg("$err");
      $err = $event->postDelete();
      if ($err!="") AddWarningMsg("$err");
    }
    sendRv($action, $event, 2, _("event deletion information message"));
  }
  redirect($action, "WGCAL", "WGCAL_CALENDAR");
}
?>