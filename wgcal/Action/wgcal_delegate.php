<?php

include_once('WGCAL/Lib.wTools.php');
include_once('FDL/Class.Doc.php');

function wgcal_delegate(&$action) 
{
  $dlist = GetHttpVars("dlist", "");

  echo "dlist = [$dlist]<br>";

  if ($dlist!="") {
    $user = new Doc($action->getParam("FREEDOM_DB"), $action->user->fid);
    if (!$user->isAffected()) AddWarningMsg(__FILE__."::".__FILE__."> User !");
    $user->deleteValue("us_wgcal_dguname");
    $user->deleteValue("us_wgcal_dguid");
    $user->deleteValue("us_wgcal_dguwid");
    $user->deleteValue("us_wgcal_dgumode");
    if ($dlist!="-") {
      $dg_name = $dg_uid = $dg_uwid = $dg_umode = array();
      $tg = explode("|", $dlist );
      foreach ($tg as $k => $v) {
	if ($v!="") {
	  $st = explode("%", $v);
	  echo " u=".$st[0]." mode=".$st[1]."<br>";
	  $dg_uid[] = $st[0];
	  $dg_umode[] = $st[1];
	  $udg = new Doc($action->getParam("FREEDOM_DB"), $st[0]);
	  $dg_name[] = $udg->getTitle();
	  $dg_uwid[] = $udg->getValue("us_whatid");
	}
      }
      if (count($dg_uid)>0) {
	$user->setValue("us_wgcal_dguname", $dg_name);
	$user->setValue("us_wgcal_dguid", $dg_uid);
	$user->setValue("us_wgcal_dguwid", $dg_uwid);
	$user->setValue("us_wgcal_dgumode", $dg_umode);
	print_r2($dg_name);
	print_r2($dg_uid);
	print_r2($dg_uwid);
	print_r2($dg_umode);
      }
    }
    $user->Modify();
  }
}

?>
