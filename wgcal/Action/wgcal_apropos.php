<?php
include_once("WHAT/Class.Param.php");

function wgcal_apropos(&$action) {

  $v = new Param($action->dbaccess, array("VERSION", PARAM_APP, $action->parent->id));
  $action->lay->set("appicon", $action->parent->icon);
  $action->lay->set("appversion", $v->val);

  return;
}
?>