<?php

include_once("WGCAL/Lib.WGCal.php");

function wgcal_portal(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $period = $action->GetParam("WGCAL_U_PORTALPERIOD", "week");

  switch($period) {
    case "3days": $delta = 24*3600*3; break;
    case "2weeks": $delta = 24*3600*14; break;
    case "month": $delta = 24*3600*31; break;
    default: $delta = 24*3600*7;
  }
  $ctime = time();
  $start = ts2db($ctime, "Y-m-d H:i:s");
  $end = ts2db(($ctime+$delta), "Y-m-d H:i:s");
  
  $action->lay->set("period", $period);
  $action->lay->set($period."sel", "selected");
  $action->lay->set("periodtext", _($period));
  $action->lay->set("tds", substr($start,0,11));
  $action->lay->set("tde", substr($end,0,11));

  $ress[] = $action->user->fid;
  if ($viewme) {
    $grp = WGCalGetRGroups($action, $action->user->id);
    foreach ($grp as $kr=>$vr) $ress[] = $vr;
  }
  setHttpVar("ds", $start);
  setHttpVar("de", $end);
  setHttpVar("rlist", implode("|",$ress));
  setHttpVar("explode", true);
  setHttpVar("mode", $action->GetParam("WGCAL_U_PORTALSTYLE", "TABLE"));
  setHttpVar("standalone", "N");
  
}
?>
