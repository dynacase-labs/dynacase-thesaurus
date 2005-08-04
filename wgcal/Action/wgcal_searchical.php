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

    $total = 0;
    $words = explode(" ", $sical);
    $sstr = "";
    foreach ($words as $kw => $vw) {
      $sstr .= (strlen($sstr)>0 ?  " OR " : "");
      $sstr .= "title ~* '".$vw."'";
    }
    $filter[0] = $sstr;
    $dbaccess = $action->getParam("FREEDOM_DB");
    $cfams = WGCalGetRessourceFamilies($dbaccess);
    foreach ($cfams as $kf => $vf) {
      if ($total>=$max) continue;
      if ($vf["id"] == "" ) continue;
      $rdoc = GetChildDoc($action->GetParam("FREEDOM_DB"), 0, 0, $max, $filter, 
			$action->user->id, "TABLE", $vf["id"]);
      foreach ($rdoc as $kd => $vd) {
	if ($total>=$max) continue;
	if (!isset($rlist[$vd["id"]]) || $vf["id"]>$rlist[$vd["id"]]["fid"]) {
	  $rlist[$vd["id"]]["fid"] = $vf["id"];
	  $rlist[$vd["id"]]["id"] = $vd["id"];
	  $rlist[$vd["id"]]["icon"] = Doc::GetIcon($vd["icon"]);
	  $rlist[$vd["id"]]["title"] = ucwords(strtolower($vd["title"]));
	  $rlist[$vd["id"]]["titlejs"] = addslashes(ucwords(strtolower($vd["title"])));

	  // Active menu items
	  PopupActive('mRess', $vd["id"], 'radd');
	  PopupActive('mRess', $vd["id"], 'rcalendar');
	  PopupActive('mRess', $vd["id"], 'rprefered');
	  PopupActive('mRess', $vd["id"], 'rclose');
	  
	  $total ++;
	}
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