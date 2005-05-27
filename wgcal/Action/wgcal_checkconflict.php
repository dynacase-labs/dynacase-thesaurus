<?php

include_once("WHAT/Lib.Common.php");
include_once("FDL/Class.Doc.php");
include_once("FDL/mailcard.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_checkconflict(&$action) {

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/DHTMLapi.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/PopupWindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");

  $db = $action->getParam("FREEDOM_DB");

  $checkForConflict = ($action->getParam("WGCAL_U_CHECKCONFLICT", 1)==1?true:false);
  $alreadyChecked = (GetHttpVars("cfchecked",0)==1?true:false);
  $event = (GetHttpVars("eventid",-1));

  if ($alreadyChecked || !$checkForConflict) {
    $action->lay->set("NOCF", true);
    $action->lay->set("CF", false);
    return;
  }

  $ds = GetHttpVars("Fstart", 0);
  $de = GetHttpVars("Fend", 0);
  $start = date2db($ds);
  $end = date2db($de);
  $htype = 0;
  if (GetHttpVars("nohour", "") == "on") {
    $htype = 1;
    $start = date2db($ds, false) . " 00:00";
    $end = date2db($de, false) . " 00:00";
  }
  if (GetHttpVars("allday", "") == "on") {
    $htype = 2;
    $start = date2db($ds, false)." 00:00";
    $end = date2db($ds, false)." 23:59";
  }
  $withme = GetHttpVars("withMe", "off");
  $attendees = array();
  $attl = GetHttpVars("attendees", "");
  if ($attl!="") $attendees = explode("|", $attl);
  if ($withme) $attendees[count($attendees)] = $action->user->fid;
  
  $conflict = array();
  
  $nrl = array();
  foreach($attendees as $k => $v) {
    $trl = GroupExplode($action, $v);
    $nrl = array_merge($nrl, $trl);
  }
  
  $tevtmp = WGCalGetAgendaEvents($action, $nrl, $start, $end);
    
  $tev = array();
  foreach ($tevtmp as $k=>$v) {
    // $line[]["line"] = "[".$v["RG"]." id=".$v["ID"]." debut=".$v["TSSTART"]." fin=".$v["TSEND"]." owner=".$v["IDP"];
    if ($v["IDP"]!=$event) $tev[] = $v;
  }
  $action->lay->setBlockData("CARDS", $tev);
  $action->lay->set("CF", (count($tev)>0 ? true : false));
  $action->lay->set("NOCF", (count($tev)>0 ? false : true));
  $action->lay->SetBlockData("CONFLICTS", $tev);

  if (count($tev)==0) $action->lay->set("NOCF", true);
  else $action->lay->set("NOCF", false);

  return;
}
?>

