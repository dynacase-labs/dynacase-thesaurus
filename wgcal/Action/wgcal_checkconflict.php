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

  $checkForConflict = ($action->getParam("WGCAL_U_CHECKCONFLICT", 1)==1?true:false);
  $alreadyChecked = (GetHttpVars("cfchecked",0)==1?true:false);
  $event = (GetHttpVars("eventid",-1));

  if ($alreadyChecked || !$checkForConflict) {
    $action->lay->set("NOCF", true);
    return;
  }

  $ds = GetHttpVars("Fstart", 0);
  $de = GetHttpVars("Fend", 0);
  $start = w_datets2db(($ds+60));
  $end = w_datets2db(($de-60));
  $htype = 0;
  if (GetHttpVars("nohour", "") == "on") {
    $htype = 1;
    $start = w_datets2db($ds, false) . " 00:00:00";
    $end = w_datets2db($ds, false) . " 00:00:00";
  }
  if (GetHttpVars("allday", "") == "on") {
    $htype = 2;
    $start = w_datets2db($ds, false)." 00:00:00";
    $end = w_datets2db($ds, false)." 23:59:59";
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
  $idres = implode("|", $nrl);
  setHttpVar("idres",$idres);

  $dbaccess = $action->getParam("FREEDOM_DB");
  $qev = getIdFromName($dbaccess,"WG_AGENDA");
  $dre=new Doc($dbaccess, $qev);

  $famr = $action->getParam("WGCAL_G_VFAM", "CALEVENT");
  $ft = explode("|", $famr);
  $fti = array();
  foreach ($ft as $k => $v)     $fti[] = (is_numeric($v) ? $v : getIdFromName($dbaccess, $v));
  $idfamref = implode("|", $fti);
  setHttpVar("idfamref", $idfamref);

  $tevtmp = array();
  $tevtmp = $dre->getEvents($start, $end);
  $tev = array();
  $itev = 0;
  if (count($tevtmp)>0) {
    $myid = $action->user->fid;
    foreach ($tevtmp as $k=>$v) {
      $ressd = wgcalGetRessourcesMatrix($v["evt_idinitiator"]);
//       echo "event=$event curev=".$v["evt_idinitiator"]." ressd[$myid]=".$ressd[$myid]." ressd[$myid][state]=".$ressd[$myid]["state"]."<br>";
      if ($v["evt_idinitiator"]!=$event && (isset($ressd[$myid]) && $ressd[$myid]["state"]!=EVST_REJECT)) {
	$d = new Doc($dbaccess, $v["evt_idinitiator"]);
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
//   print_r2($tev);
  if (count($tev)==0) $action->lay->set("NOCF", true);
  else $action->lay->set("NOCF", false);
  
  return;
}
?>

