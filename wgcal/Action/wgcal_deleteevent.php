<?php

include_once("FDL/Class.Doc.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_deleteevent(&$action, $optev=-1) {

  global $_SERVER;
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
    $title = $action->getParam("WGCAL_G_MARKFORMAIL", "[RDV]")." ".$event->getValue("calev_evtitle");
    sendRv($action, $event, 2, $title, _("event deletion information message"));
  }
//   Header("Location: ".$_SERVER["HTTP_REFERER"]);
   redirect($action, "WGCAL", "WGCAL_CALENDAR");
}
?>