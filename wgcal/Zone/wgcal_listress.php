<?php
include_once("Lib.WGCal.php");
include_once("FDL/Class.Doc.php");

function wgcal_listress(&$action)
{

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $ico = "down";
  $vis = "none";
  $state = WGCalToolIsVisible($action, CAL_T_CALSELECTOR);
  if ($state) {
    $ico = "up";
    $vis = "";
  }
  $action->lay->set("VISICO",$ico);
  $action->lay->set("VISTOOL",$vis);


  $i = 0;
  $j = 0;

  $found = false;
  $lress = explode("|", $action->GetParam("WGCAL_U_RESSLISTED", ""));
  if (count($lress)>0) {
    foreach ($lress as $k => $v) {
      $tt = explode("%", $v);
      $rid = $tt[0];
      $sid = ($tt[1]!="" ? $tt[1] : 0);
      $cid = ($tt[2]!="" ? $tt[2] : "blue");
      if ($rid == $action->user->fid) $found = true;
      $rd = new Doc($dbaccess, $rid);
      if ($rd->IsAffected()) {
	$t[$i]["RID"] = $rd->id;
	$t[$i]["RDESCR"] = $rd->title;
	$t[$i]["RICON"] = $rd->getIcon();
	$t[$i]["RCOLOR"] = $cid;
	if ($sid==1 || $rid == $action->user->fid) $t[$i]["RSTYLE"] = "WGCRessSelected";
	else $t[$i]["RSTYLE"] = "WGCRessDefault";
	$i++;
      }
    }
  }
  if (!$found) {
    $rd = new Doc($dbaccess, $action->user->fid);
    $t[$i]["RID"] = $rd->id;
    $t[$i]["RDESCR"] = $rd->title;
    $t[$i]["RICON"] = $rd->getIcon();
    $t[$i]["RCOLOR"] = "white";
    $t[$i]["RSTYLE"] = "WGCRessSelected";
    $i++;
  }
  $action->lay->SetBlockData("L_RESS", $t);
}
?>
