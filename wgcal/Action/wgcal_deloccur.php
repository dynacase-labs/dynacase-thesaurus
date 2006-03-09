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
  if ($event->isAffected() && $occ!="") {
    $tocc = $event->getTValue("CALEV_EXCLUDEDATE");
    $tnocc = array();
    foreach ($tocc as $k => $v) {
      if (substr($v,0,11) != substr($occ,0,11)) $tnocc[] = $v;
    }
    $tnocc[] = $occ;
    $event->setValue("CALEV_EXCLUDEDATE",$tnocc );
    $err = $event->Modify();
    if ($err!="") AddWarningMsg("$err");
    else {
      $err = $event->PostModify();
      if ($err!="") AddWarningMsg("$err");
    }
    $event->AddComment(_("delete repeat occurrence for ").substr($occ,0,11));
    $mail_msg = _("event time modification message");
    $mail_who = 2;
    $comment = _("event modification time");
    $title = $action->getParam("WGCAL_G_MARKFORMAIL", "[RDV]")." ".$event->getValue("calev_evtitle");
    sendRv($action, $event, $mail_who, $title, $mail_msg, true);
  }
  redirect($action, "WGCAL", "WGCAL_CALENDAR");
}
    
?>