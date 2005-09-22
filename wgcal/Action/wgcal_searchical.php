<?php
include_once("EXTERNALS/WGCAL_external.php");
include_once('FDL/popup_util.php');
include_once('WGCAL/Lib.wTools.php');

function wgcal_searchical(&$action) {
  $sical = GetHttpVars("sical", "");

  $max = 25;

  $action->lay->set("max", $max);
  $action->lay->set("sical", $sical);

  $rlist = array();
  if ($sical!="") {

    // Init popup
    $action->lay->set("POPUPICONS", $action->getParam("WGCAL_U_ICONPOPUP", true));
    include_once("FDL/popup_util.php");
    popupInit('mRess',  array('radd', 'rcalendar', 'rprefered', 'rclose'));


    $tg = wGetUserGroups();
    foreach ($tg as $k => $v) $mygroups[] = $k;
    $rdoc = wSearchUserCal(-1, $mygroups, 0, $sical, $max);
    foreach ($rdoc as $kd => $vd) {
      if (!isset($rlist[$vd["id"]])) {
	$rlist[$vd["id"]]["fid"] = $vf["id"];
	$rlist[$vd["id"]]["id"] = $vd["id"];
	$rlist[$vd["id"]]["icon"] = Doc::GetIcon($vd["icon"]);
	$rlist[$vd["id"]]["title"] = ucwords(strtolower($vd["title"]));
	$rlist[$vd["id"]]["titlejs"] = addslashes(ucwords(strtolower($vd["title"])));
	$rlist[$vd["id"]]["romode"] = (wGetUserCalAccessMode($vd)==1?"false":"true");

	// Active menu items
	PopupActive('mRess', $vd["id"], 'radd');
	PopupActive('mRess', $vd["id"], 'rcalendar');
	PopupActive('mRess', $vd["id"], 'rprefered');
	PopupActive('mRess', $vd["id"], 'rclose');
      }
    }
    if (count($rlist)>0) {
      wUSort($rlist, "title");
      popupGen(count($rlist));
      $contacts = $action->GetParam("WGCAL_U_PREFRESSOURCES", "");
      $tcontacts = explode("|", $contacts);
      $rplist = "";
      if (count($tcontacts)>0) {
	foreach ($tcontacts as $kc => $vc) {
	  if ($vc=="") continue;
	  $rplist .= (strlen($vc)>0?"|":"").$vc;
	}
      }
    } else {
      $rplist = null;
    }
    $action->lay->set("rplist", $rplist);
    $action->lay->setBlockData("rlist", $rlist);
    $action->lay->setBlockData("jsRlist", $rlist);
  }
  $action->lay->set("showrlist", (count($rlist)>0?true:false));
}
?>
