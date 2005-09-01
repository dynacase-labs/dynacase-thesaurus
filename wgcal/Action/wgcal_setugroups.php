<?php
include_once('WGCAL/Lib.wTools.php');
include_once('FDL/Class.Doc.php');

function wgcal_setugroups(&$action) 
{
  $gfid = GetHttpVars("ugroups", "");
  $user = new Doc($action->getParam("FREEDOM_DB"), $action->user->fid);
  $user->deleteValue("us_wgcal_gid");
  if ($gfid!="") {
    $tgv = array();
    $tgvw = array();
    $tgvname = array();
    $tg = explode("|",  $gfid);
    foreach ($tg as $k => $v) {
      if ($v!="") {
	$tgv[] = $v;
	$dg = new Doc($action->getParam("FREEDOM_DB"), $v);
	$tgvw[] = $dg->getValue("us_whatid");
	$tgvname[] = $dg->getTitle();
      }
    }
    if (count($tgv)>0) {
      $user->setValue("us_wgcal_gid", $tgv);
      $user->setValue("us_wgcal_gwid", $tgvw);
      $user->setValue("us_wgcal_gname", $tgvname);
    }
  }
  $user->Modify();
}

?>