<?php

include_once('WGCAL/Lib.wTools.php');
include_once('FDL/Class.Doc.php');

function wgcal_vcalmode(&$action) 
{
  $vcalmode = GetHttpVars("vcalmode", 0);
  $vcalgroups = GetHttpVars("vcalgroups", "");

  $user = new Doc($action->getParam("FREEDOM_DB"), $action->user->fid);
  $user->setValue("us_wgcal_vcalgrpmode", $vcalmode);
  $user->deleteValue("us_wgcal_vcalgrpid");
  $user->deleteValue("us_wgcal_vcalgrpwid");
  $user->deleteValue("us_wgcal_vcalgrpwrite");
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
	$dg = new Doc($action->getParam("FREEDOM_DB"), $st[0]);
	$tgvname[] = $dg->getTitle();
	$tgvwid[] = $dg->getValue("us_whatid");
      }
    }
    if (count($tgvid)>0) {
      $user->setValue("us_wgcal_vcalgrpname", $tgvname);
      $user->setValue("us_wgcal_vcalgrpid", $tgvid);
      $user->setValue("us_wgcal_vcalgrpwid", $tgvwid);
      $user->setValue("us_wgcal_vcalgrpwrite", $tgvmode);
    }
  }
  $user->Modify();
}

?>
