<?php
include_once('WGCAL/Lib.wTools.php');
function wgcal_addgroups(&$action) {
  $gfid = GetHttpVars("addgfid", -1);
  if ($gfid!=-1) wAddGroups($gfid);
  redirect($action, "WGCAL", "WGCAL_CHOOSEGROUPS");
}

?>