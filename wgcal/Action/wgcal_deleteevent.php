<?php

include_once("FDL/Class.Doc.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_deleteevent(&$action, $optev=-1) {

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $evi = GetHttpVars("ev", -1);
  $cev = GetHttpVars("cev", -1);
  if ($evi==-1 && $cev==-1 && $optev!=-1) $cev = $optev;
  $event = GetCalEvent($dbaccess, $evi, $cev);
  if (!$event) {
    $action->AddMsgWarning("No event # $evi $cev ");
    
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