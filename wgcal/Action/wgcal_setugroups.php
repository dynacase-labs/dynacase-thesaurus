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
    $tg = explode("|",  $gfid);
    foreach ($tg as $k => $v) {
      if ($v!="") $tgv[] = $v;
    }
    if (count($tgv)>0) $user->setValue("us_wgcal_gid", $tgv);
  }
  $user->Modify();
}

?>
