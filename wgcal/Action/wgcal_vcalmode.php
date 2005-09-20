<?php

include_once('WGCAL/Lib.wTools.php');
include_once('WGCAL/Class.UCalVis.php');
include_once('FDL/Class.Doc.php');

function wgcal_vcalmode(&$action) 
{
  $vcalmode = GetHttpVars("vcalmode", 0);
  $vcalgroups = GetHttpVars("vcalgroups", "");
  $vcalid = GetHttpVars("vcalid", 0);

  $defaultGroup = 2;
  
  $user = new_Doc($action->getParam("FREEDOM_DB"), $action->user->fid);
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
	$dg = new_Doc($action->getParam("FREEDOM_DB"), $st[0]);
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
  $err = $user->Modify();
  $utmp = new UCalVis($action->getParam("FREEDOM_DB"));
  $utmp->cleanUCal($action->user->fid,  0);
  if ($vcalmode!=1) {
    $tgr = wSpeedWFidGroups();
    $ucalv = new UCalVis($action->getParam("FREEDOM_DB"), array(0, $action->user->fid,  $tgr["byWid"][2]));
    $ucalv->ucalvis_ucal = $vcalid;
    $ucalv->ucalvis_ufid = $action->user->fid; 
    $ucalv->ucalvis_uwid = $action->user->id;  
    $ucalv->ucalvis_gfid = $tgr["byWid"][$defaultGroup]; 
    $ucalv->ucalvis_gwid = $defaultGroup; 
    $ucalv->ucalvis_mode = 1;
    $ucalv->Add();
  } else {   
    if ( count($tgvid)>0) {
      foreach ($tgvid as $k => $v) {
	$ucalv = new UCalVis($action->getParam("FREEDOM_DB"), array(0, $action->user->fid,  $v));
	if (!$ucalv->isAffected()) {
	  $ucalv->ucalvis_ucal = $vcalid;
	  $ucalv->ucalvis_gfid = $v; 
	  $ucalv->ucalvis_ufid = $action->user->fid; 
	  $ucalv->ucalvis_uwid = $action->user->id;  
	  $ucalv->ucalvis_gwid = $tgvwid[$k]; 
	  $ucalv->ucalvis_mode = ($tgvmode[$k] ? 1 : 0);
	  $ucalv->Add();
	} else {
	  $ucalv->ucalvis_uwid = $action->user->id;  
	  $ucalv->ucalvis_gwid = $tgvwid[$k]; 
	  $ucalv->ucalvis_mode = ($tgvmode[$k] ? 1 : 0);
	  $ucalv->Modify();
	}
      }
    }
  }
}

?>
