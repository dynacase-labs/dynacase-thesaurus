<?php

include_once("WHAT/Lib.Common.php");
include_once("FDL/Class.Doc.php");
include_once("EXTERNALS/WGCAL_external.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_seteventstate(&$action) {

  $db = $action->getParam("FREEDOM_DB");
  $evi = GetHttpVars("id", -1);
  $forowner = GetHttpVars("ow", 0);
  $forowner = ($forowner==0?$action->user->fid:$forowner);
  $event = new_Doc($db, $evi);
  $evstate  = GetHttpVars("st", -1);
  $action->lay->set("showevent", false);
  

  if (!$event->isAffected() || $evstate==-1) {
    $action->lay->set("status", -1);
    $action->lay->set("statustext", "wgcal_seteventstate: error, can't find event #$evi");
    return;
  } else {
    $found = false;
    $ress = "";
    $att_id    = $event->getTValue("CALEV_ATTID", array());
    $att_state = $event->getTValue("CALEV_ATTSTATE", array());
    $att_title = $event->getTValue("CALEV_ATTTITLE", array());
    foreach ($att_id as $ka => $va) {
      if ($va == $forowner) {
	$found = true;
	$att_state[$ka] = $evstate;
	$ress = $att_title[$ka];
      }
    }
    
    if ($found) {
      $event->disableEditControl();
      $event->setValue("CALEV_ATTSTATE", $att_state); 
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

      $uw = new_Doc($dbaccess, $event->getValue("calev_ownerid"));
      $wuid = $uw->getValue("us_whatid");
      if ($action->parent->param->GetUParam("WGCAL_U_MAILCHGSTATE", $wuid) == 1) {
	sendRv($action, $event, 0, _("state set to ").WGCalGetLabelState($evstate)." par ".ucfirst($action->user->lastname)." ".ucfirst($action->user->firstname));
      }
      $event->AddComment(_("state set to ").WGCalGetLabelState($evstate));
      $event->enableEditControl();
    }
  }
}


?>