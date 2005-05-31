<?php

include_once("WHAT/Lib.Common.php");
include_once("FDL/Class.Doc.php");
include_once("EXTERNALS/WGCAL_external.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_seteventstate(&$action) {

  $db = $action->getParam("FREEDOM_DB");
  $evi = GetHttpVars("ev", -1);
  $cev = GetHttpVars("cev", -1);
  $raction = GetHttpVars("ra", "WGCAL_CALENDAR");
  $event = GetCalEvent($db, $evi, $cev);
  $evstate  = GetHttpVars("st", -1);
  if (!$event || $evstate==-1) return;

  $found = false;
  $ress = "";
  $att_id    = $event->getTValue("CALEV_ATTID", array());
  $att_state = $event->getTValue("CALEV_ATTSTATE", array());
  $att_title = $event->getTValue("CALEV_ATTTITLE", array());
  foreach ($att_id as $ka => $va) {
    if ($va == $action->user->fid) {
      $found = true;
      $att_state[$ka] = $evstate;
      $ress = $att_title[$ka];
    }
  }
  if ($found) {
    $event->disableEditControl();
    $event->setValue("CALEV_ATTSTATE", $att_state); 
    $err = $event->Modify();
    if ($err!="") AddWarningMsg("$err");
    else {
      $err = $event->PostModify();
      if ($err!="") AddWarningMsg("$err");
    }
    sendRv($action, $event, 0, ucfirst($action->user->lastname)." ".ucfirst($action->user->firstname)." : "._("state set to ").WGCalGetLabelState($evstate));
    $event->AddComment(_("state set to ").WGCalGetLabelState($evstate));
    $event->enableEditControl();
  }
  redirect($action, "WGCAL", $raction);
}


?>