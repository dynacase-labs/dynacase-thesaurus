<?php

function wgcal_usetparam(&$action) {
  $pname  = GetHttpVars("pname", "");
  $pvalue = GetHttpVars("pvalue", "");
  $psess  = GetHttpVars("psession", 0);
  if ($pname != "") {
    $action->parent->param->set($pname, $pvalue, PARAM_USER.$action->user->id, $action->parent->id);
  }
}
?>