<?php

include_once("WHAT/Lib.Common.php");
include_once("FDL/Class.Doc.php");
include_once("EXTERNALS/WGCAL_external.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_deloccur(&$action) {

  $db = $action->getParam("FREEDOM_DB");
  $occ  = GetHttpVars("evocc", "");
  $evi = GetHttpVars("id", -1);
  $event = new_Doc($db, $evi);
  if (!$event->isAffected()) {
    $action->lay->set("status", -1);
    $action->lay->set("statustext", "wgcal_seteventstate: error, can't find event #$evi");
    return;
  } 
  $action->lay->set("showevent", false);
    
  $tocc = $event->getTValue("CALEV_EXCLUDEDATE");
  $tnocc = array();
  foreach ($tocc as $k => $v) {
    if (substr($v,0,11) != substr($occ,0,11)) $tnocc[] = $v;
  }
  $tnocc[] = $occ;
  $event->setValue("CALEV_EXCLUDEDATE",$tnocc );
  $err = $event->Modify();
  if ($err!="") {
    $action->lay->set("status", -1);
    $action->lay->set("statustext", "Freedom internal error doc->modify(): $err");
    return;
  } else {
    $err = $event->PostModify();
    if ($err!="") {
      $action->lay->set("status", -1);
      $action->lay->set("statustext", "Freedom internal error doc->PostModify(): $err");
      return;
    }
  }
  $event->AddComment(_("delete repeat occurrence for ").substr($occ,0,11));
  $mail_msg = _("event time modification message");
  $mail_who = 2;
  $comment = _("event modification time");
  $title = $action->getParam("WGCAL_G_MARKFORMAIL", "[RDV]")." ".$event->getValue("calev_evtitle");
  sendRv($action, $event, $mail_who, $title, $mail_msg, true);
  

 // Get produced event
  $ev = wGetSinglePEvent($event->id);
  $action->lay->setBlockData("modEvents", $ev);
  $action->lay->set("status", 0);
  $action->lay->set("count", count($ev));
  $action->lay->set("statustext", "#".$event->id." ".($new?"created":"updated"));
  $action->lay->set("showevent", true);
}
    
?>