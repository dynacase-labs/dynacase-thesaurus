<?php

function faddbook_setuparam(&$action) {
  $param = GetHttpVars("pname", "");
  $value = GetHttpVars("pvalue", "");
  $rapp = GetHttpVars("rapp", "");
  $raction = GetHttpVars("raction", "");
  $action->parent->param->set($param, $value, PARAM_USER.$action->user->id, $action->parent->id);
  if ($rapp=="" || $raction=="") return;
  redirect($action, $rapp, $raction);
}
?>