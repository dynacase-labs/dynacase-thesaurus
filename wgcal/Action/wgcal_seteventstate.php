<?php

include_once("WHAT/Lib.Common.php");
include_once("FDL/Class.Doc.php");
include_once("EXTERNALS/WGCAL_external.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_seteventstate(&$action) {

  $db = $action->getParam("FREEDOM_DB");
  $evi = GetHttpVars("id", -1);
  $forowner = GetHttpVars("owner", 0);
  $event = new_Doc($db, $evi);
  $evstate  = GetHttpVars("st", -1);

  global $_SERVER;
  // print_r2($_SERVER);
  // [HTTP_REFERER] => http://obeone.tlse.i-cesam.com/what/index.php?sole=Y&&app=WGCAL&action=WGCAL_PORTAL

  if (!$event->isAffected() || $evstate==-1) {
    AddWarningMsg("wgcal_seteventstate: error, can't find event #$evi");
  } else {

    $attchange = ($forowner==1 ? $event->getValue("calev_ownerid") : $action->user->fid );

    $raction = GetHttpVars("ra", "WGCAL_CALENDAR");
    $found = false;
    $ress = "";
    $att_id    = $event->getTValue("CALEV_ATTID", array());
    $att_state = $event->getTValue("CALEV_ATTSTATE", array());
    $att_title = $event->getTValue("CALEV_ATTTITLE", array());
    foreach ($att_id as $ka => $va) {
      if ($va == $attchange) {
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

      $uw = new_Doc($dbaccess, $event->getValue("calev_ownerid"));
      $wuid = $uw->getValue("us_whatid");
      if ($action->parent->param->GetUParam("WGCAL_U_MAILCHGSTATE", $wuid) == 1) {
	sendRv($action, $event, 0, _("state set to ").WGCalGetLabelState($evstate)." par ".ucfirst($action->user->lastname)." ".ucfirst($action->user->firstname));
      }
      $event->AddComment(_("state set to ").WGCalGetLabelState($evstate));
      $event->enableEditControl();
    }
  }
  redirect($action, "WGCAL", "WGCAL_CALENDAR");
}


?>