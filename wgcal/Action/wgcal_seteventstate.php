<?php

include_once("WHAT/Lib.Common.php");
include_once("FDL/Class.Doc.php");
include_once("WGCAL/WGCAL_external.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_seteventstate(&$action) {

  $db = $action->getParam("FREEDOM_DB");
  $ev     = GetHttpVars("ev", -1);
  if ($ev==-1) $evid = -1;
  else {
    $evtmp = new Doc($db, $ev);
    $evid = $evtmp->getValue("evt_idinitiator");
  }
  $evstate  = GetHttpVars("st", -1);
  if ($evid<1 || $evstate==-1) return;

  $ress = "";
  $event = new Doc($db, $evid);
  $att_id    = $event->getTValue("CALEV_ATTID", array());
  $att_state = $event->getTValue("CALEV_ATTSTATE", array());
  $att_title = $event->getTValue("CALEV_ATTTITLE", array());
  foreach ($att_id as $ka => $va) {
    if ($va == $action->user->fid) {
      $att_state[$ka] = $evstate;
      $ress = $att_title[$ka];
    }
  }
  $event->setValue("CALEV_ATTSTATE", $att_state); 
    
  $err = $event->Modify();
   if ($err!="") AddWarningMsg("$err");
   else {
     $err = $event->PostModify();
     if ($err!="") AddWarningMsg("$err");
   }
   sendRv($action, $event);
   $event->AddComment(_("state set to ").WGCalGetLabelState($evstate));
   redirect($action, "WGCAL", "WGCAL_CALENDAR");
}

?>