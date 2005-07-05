<?php
include_once('WGCAL/Lib.wTools.php');
function wgcal_delgroups(&$action) {
  $gfid = GetHttpVars("delgfid", -1);
  if ($gfid!=-1) wDelGroups($gfid);
  redirect($action, "WGCAL", "WGCAL_CHOOSEGROUPS");
}

?>