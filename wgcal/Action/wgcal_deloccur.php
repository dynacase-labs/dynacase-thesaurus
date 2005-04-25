<?php

include_once("WHAT/Lib.Common.php");
include_once("FDL/Class.Doc.php");
include_once("WGCAL/WGCAL_external.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_deloccur(&$action) {

  $db = $action->getParam("FREEDOM_DB");
  $evi = GetHttpVars("ev", -1);
  $cev = GetHttpVars("cev", -1);
  $event = GetCalEvent($db, $evi, $cev);
  $occ  = GetHttpVars("evocc", "");
  if (!$event || $evstate==-1 || $occ=="") return;

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
   redirect($action, "WGCAL", "WGCAL_CALENDAR");
}
    
?>