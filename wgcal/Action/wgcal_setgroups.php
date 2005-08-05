<?php
include_once('WGCAL/Lib.wTools.php');
function wgcal_setgroups(&$action) {
  $gfid = GetHttpVars("groups", "");
  wSetGroups($gfid);
}

?>