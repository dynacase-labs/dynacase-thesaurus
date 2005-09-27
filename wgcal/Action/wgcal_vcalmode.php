<?php

include_once('WGCAL/Lib.wTools.php');
include_once('FDL/Class.Doc.php');

function wgcal_vcalmode(&$action) 
{
  $vcalmode = GetHttpVars("vcalmode", 0);
  $vcalgroups = GetHttpVars("vcalgroups", "");
  $vcalid = GetHttpVars("vcalid", 0);

  $defaultGroup = 2;
  

  $cal = new_Doc($action->getParam("FREEDOM_DB"), $vcalid);
  if (!$cal->isAffected()) return;


  $cal->setValue("agd_vgroupmode", $vcalmode);
  $cal->deleteValue("agd_vgroupwid");
  $cal->deleteValue("agd_vgroupfid");
  $cal->deleteValue("agd_vgrouprw");
  $cal->deleteValue("agd_vgroupname");
  if ($vcalgroups!="") {
    $tgvid = array();
    $tgvwid = array();
    $tgvname = array();
    $tgvmode = array();
    $tg = explode("|",  $vcalgroups);
    foreach ($tg as $k => $v) {
      if ($v!="") {
	$st = explode("%", $v);
	$tgvid[] = $st[0];
	$tgvmode[] = $st[1];
	$dg = getTDoc($action->getParam("FREEDOM_DB"), $st[0]);
	$tgvname[] = $dg["title"];
	$tgvwid[] = $dg["us_whatid"];
      }
    }
    if (count($tgvid)>0) {
      $cal->setValue("agd_vgroupname", $tgvname);
      $cal->setValue("agd_vgroupfid", $tgvid);
      $cal->setValue("agd_vgroupwid", $tgvwid);
      $cal->setValue("agd_vgrouprw", $tgvmode);
    }
  }
  $err = $cal->Modify();
  $cal->ComputeAccess();
  return;
}

?>
