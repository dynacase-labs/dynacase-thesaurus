<?php

include_once("WHAT/Lib.Common.php");
include_once("FDL/Class.Doc.php");
include_once("WGCAL/WGCAL_external.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_seteventstate(&$action) {

  $db = $action->getParam("FREEDOM_DB");
  $evid     = GetHttpVars("evid", -1);
  $evstate  = GetHttpVars("evstate", -1);
  if ($evid<1 || $evstate==-1) return;

  switch ($evstate) 
    {
    case 1: $realstate = EVST_ACCEPT; break;
    case 0: $realstate = EVST_REJECT; break;
    default: return;
    }
 
  $event = new Doc($db, $evid);
  $att_id    = $event->getTValue("CALEV_ATTID", array());
  $att_state = $event->getTValue("CALEV_ATTSTATE", array());
  foreach ($att_id as $ka => $va) {
    if ($va == $action->user->fid) $att_state[$ka] = $realstate;
  }
  $event->setValue("CALEV_ATTSTATE", $att_state); 
    
  $err = $event->Modify();
   if ($err!="") AddWarningMsg("$err");
   else {
     $err = $event->PostModify();
     if ($err!="") AddWarningMsg("$err");
   }
   sendEventMail($action, $evid);
}

?>
