<?php
include_once("Lib.WGCal.php");
include_once("FDL/Class.Doc.php");

function wgcal_listress(&$action)
{
  WGCalToolInitState($action, CAL_T_CALSELECTOR);
  $i = 0;
  $j = 0;

  $lress = explode("|", $action->GetParam("WGCAL_U_RESSDISPLAYED", ""));
  while (list($k, $v) = each($lress)) {
    $t = explode("^", $v);
    $rid = $t[0];
    $sid = ($t[1]!="" ? $t[1] : 0);
    $cid = ($t[2]!="" ? $t[2] : "blue");
    $rd = new Doc($dbaccess, $rid);
    if ($rd->IsAffected()) {
      $t[$i]["RID"] = $rd->id;
      $t[$i]["RDESCR"] = $rd->title;
      $t[$i]["RICON"] = $rd->getIcon();
      $t[$i]["RCOLOR"] = $cid;
      if ($sid==0) $t[$i]["RSTYLE"] = "cal_unselected";
      else $t[$i]["RSTYLE"] = "cal_selected";
      $i++;
    }
  }
  $action->lay->SetBlockData("L_RESS", $t);
}
?>
