<?php

include_once("FDL/Class.Doc.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_deleteevent(&$action, $optev=-1) {

  global $_SERVER;
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $action->lay->set("showevent", false);  

  $evi = GetHttpVars("id", -1);
  $event = new_Doc($dbaccess, $evi);
  if (!$event->isAffected()) {
    $action->log->error("wgcal_deleteevent: error, can't find event #$evi");
    return;
  } 

  sendRv($action, $event, 2, $title, _("event deletion information message"));

  $err = $event->Delete();
  if ($err!="") {
    $action->log->error("wgcal_deleteevent: FreedomDoc(#$evi) internal error doc->delete(): $err");
    return;
  }

  $err = $event->postDelete();
  if ($err!="") {
    $action->log->error("wgcal_deleteevent: FreedomDoc(#$evi) internal error doc->postdelete(): $err");
    return;
  }
  return;
}
?>