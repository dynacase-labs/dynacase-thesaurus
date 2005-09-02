<?php

include_once("WGCAL/Lib.WGCal.php");
include_once('WHAT/Lib.Http.php');
include_once('WGCAL/wgcal_gview.php');

function wgcal_portal(&$action) {
  
  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_calendar.js");
  
  $dr   = GetHttpVars("dr", 0); // Test drift
 
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  $period = $action->GetParam("WGCAL_U_PORTALPERIOD", "week");
  
  switch($period) {
  case "3days": $delta = 24*3600*4; break;
  case "2weeks": $delta = 24*3600*15; break;
  case "month": $delta = 24*3600*32; break;
  default: $delta = 24*3600*8;
  }
  $ctime = time() - ($dr*24*3600) ;
  $start = ts2db($ctime, "Y-m-d H:i:s");
  $end = ts2db(($ctime+$delta), "Y-m-d H:i:s");
  
  $action->lay->set("period", $period);
  $action->lay->set($period."sel", "selected");
  $action->lay->set("periodtext", _($period));
  $action->lay->set("tds", strftime("%d %B %Y",$ctime));
  $action->lay->set("tde", strftime("%d %B %Y",($ctime+$delta)));
  
  $ress = $action->user->fid;

  setHttpVar("rvfs_withme",1);
  setHttpVar("rvfs_int",$start."=".$end);
  setHttpVar("standalone",0);
  setHttpVar("explode",1);
  
  wgcal_gview($action);
}
?>
