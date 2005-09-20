<?php

include_once("WHAT/Lib.Common.php");
include_once("FDL/Class.Doc.php");
include_once("FDL/mailcard.php");
include_once("WGCAL/Lib.wTools.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_checkconflict(&$action) {

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/DHTMLapi.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/PopupWindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");

  $db = $action->getParam("FREEDOM_DB");

  $checkForConflict = $action->getParam("WGCAL_U_CHECKCONFLICT", 1);
  $ownerid = GetHttpVars("ownerid");
  $alreadyChecked = GetHttpVars("cfchecked",0);
  $event = (GetHttpVars("eventid",-1));

  if ($alreadyChecked==1 || $checkForConflict==0) {
    $action->lay->set("NOCF", true);
    return;
  }
   
  $ds = GetHttpVars("Fstart", 0);
  $de = GetHttpVars("Fend", 0);
  $start = ts2db(($ds+60), "Y-m-d H:i:s");
  $end = ts2db(($de-60), "Y-m-d H:i:s");
  if (GetHttpVars("nohour", "") == "on") {
    $start = ts2db(($ds+60), "Y-m-d 00:00:00");
    $end = ts2db(($ds-60), "Y-m-d 00:00:00");
  }
  if (GetHttpVars("allday", "") == "on") {
    $start = ts2db(($ds+60), "Y-m-d 00:00:00");
    $end = ts2db(($ds-60), "Y-m-d 23:59:59");
  }
  $withme = GetHttpVars("evwithme", "1");
  $attendees = array();
  $attl = GetHttpVars("attendees", "");
  if ($attl!="") $attendees = explode("|", $attl);
  if ($withme==1) $attendees[count($attendees)] = $ownerid;
  
  $conflict = array();
  
  $nrl = array();
  foreach($attendees as $k => $v) {
    $trl = GroupExplode($action, $v);
    $nrl = array_merge($nrl, $trl);
  }
  $idres = implode("|", $nrl);
  setHttpVar("ress",$idres);

  $tevtmp = wGetEvents($start, $end);
  $tev = array();
  $itev = 0;
  if (count($tevtmp)>0) {
    foreach ($tevtmp as $k=>$v) {
      $ressd = wgcalGetRessourcesMatrix($v["IDP"]);
//       if ($v["IDP"]!=$event && (isset($ressd[$ownerid]) && $ressd[$ownerid]["state"]!=EVST_REJECT)) {
      if ($v["IDP"]!=$event) {
	if ((isset($ressd[$ownerid]) && $ressd[$ownerid]["state"]==EVST_REJECT)) continue;
	$d = new_Doc($dbaccess, $v["IDP"]);
	$tev[$itev]["ID"] = $itev;
	$tev[$itev]["EvRCard"] = $d->viewDoc($d->defaultabstract);
	$tev[$itev]["EvPCard"] = $d->viewDoc($d->defaultview);
	$itev++;
      }
    }
    $action->lay->setBlockData("CARDS", $tev);
    $action->lay->set("NOCF", (count($tev)>0 ? false : true));
    $action->lay->SetBlockData("CONFLICTS", $tev);
  }
  if (count($tev)==0) $action->lay->set("NOCF", true);
  else $action->lay->set("NOCF", false);
  
  return;
}
?>

