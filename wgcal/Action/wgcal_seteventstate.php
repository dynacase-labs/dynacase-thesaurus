<?php

include_once("WHAT/Lib.Common.php");
include_once("FDL/Class.Doc.php");
include_once("EXTERNALS/WGCAL_external.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_seteventstate(&$action) {

  $db = $action->getParam("FREEDOM_DB");
  $evi = GetHttpVars("id", -1);
  $event = new Doc($db, $evi);
  $evstate  = GetHttpVars("st", -1);

  if (!$event->isAffected() || $evstate==-1) return;

  $raction = GetHttpVars("ra", "WGCAL_CALENDAR");
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

    $uw = new Doc($dbaccess, $event->getValue("calev_ownerid"));
    $wuid = $uw->getValue("us_whatid");
    if ($action->parent->param->GetUParam("WGCAL_U_MAILCHGSTATE", $wuid) == 1) {
      sendRv($action, $event, 0, _("state set to ").WGCalGetLabelState($evstate)." par ".ucfirst($action->user->lastname)." ".ucfirst($action->user->firstname));
    }
    $event->AddComment(_("state set to ").WGCalGetLabelState($evstate));
    $event->enableEditControl();
  }
  redirect($action, "WGCAL", $raction);
}


?>