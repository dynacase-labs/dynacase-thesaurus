<?php

include_once("WGCAL/Lib.WGCal.php");
include_once('WHAT/Lib.Http.php');
include_once('WGCAL/wgcal_gview.php');

function wgcal_portal(&$action) {
  
  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_calendar.js");
  
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $lmode = (GetHttpVars("mo", "")=="L" ? true : false );
  $period = GetHttpVars("period", "");
  if ($period=="") $period = $action->GetParam("WGCAL_U_PORTALPERIOD", "week");
  
  switch($period) {
  case "3days":  $delta = 24*3600*4; break;
  case "2weeks": $delta = 24*3600*15; break;
  case "month":  $delta = 24*3600*31; break;
  default:       $delta = 24*3600*8;
  }

  $stime = mktime( 0, 0, 0, strftime("%m",time()), strftime("%d",time()), strftime("%Y",time()));
  $etime = $stime + $delta;

  Redirect($action, "WGCAL", "WGCAL_GVIEW&rvfs_ress=".$action->user->fid."&rvfs_ts=".$stime."&rvfs_te=".$etime."&mo=L&sd=".GetHttpVars("sd", "Y"));
}
?>
