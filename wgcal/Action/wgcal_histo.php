<?php


function wgcal_histo(&$action) {

  $id = GetHttpVars("id", -1);
  $action->lay->set("id", $id);

}
?>
