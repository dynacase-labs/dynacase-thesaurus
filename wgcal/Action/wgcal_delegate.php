<?php

include_once('WGCAL/Lib.wTools.php');
include_once('WGCAL/Lib.Agenda.php');
include_once('FDL/Class.Doc.php');

function wgcal_delegate(&$action) 
{
  $dlist = GetHttpVars("dlist", "");
  $dmail = GetHttpVars("dmail", -1);


//   echo "dlist=[$dlist] dmail=[$dmail]<br>"; return;

  $user = new_Doc($action->getParam("FREEDOM_DB"), $action->user->fid);
  $dcal = getUserPublicAgenda($user->id, false);
  if ($dmail!=-1) $dcal->setValue("agd_dmail", $dmail);
  if ($dlist!="") {
    if (!$dcal->isAffected()) AddWarningMsg(__FILE__."::".__FILE__."> Calendar !");
    $dcal->deleteValue("agd_dname");
    $dcal->deleteValue("agd_dfid");
    $dcal->deleteValue("agd_dwid");
    $dcal->deleteValue("agd_dmode");
    if ($dlist!="-") {
      $dg_name = $dg_uid = $dg_uwid = $dg_umode = array();
      $tg = explode("|", $dlist );
      foreach ($tg as $k => $v) {
	if ($v!="") {
	  $st = explode("%", $v);
	  $dg_uid[] = $st[0];
	  $dg_umode[] = $st[1];
	  $udg = new_Doc($action->getParam("FREEDOM_DB"), $st[0]);
	  $dg_name[] = $udg->getTitle();
	  $dg_uwid[] = $udg->getValue("us_whatid");
	}
      }
      if (count($dg_uid)>0) {
	print_r2($dg_name);
	$dcal->setValue("agd_dname", $dg_name);
	$dcal->setValue("agd_dfid", $dg_uid);
	$dcal->setValue("agd_dwid", $dg_uwid);
	$dcal->setValue("agd_dmode", $dg_umode);
      }
    }
  }

  $dcal->Modify();
  $dcal->ComputeAccess();
}

?>
