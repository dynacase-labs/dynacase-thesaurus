<?php

function wgcal_usetparam(&$action) {
  $uid  = GetHttpVars("uid", -1);
  if ($uid==-1) $uid = $action->user->id;
  $pname  = GetHttpVars("pname", "");
  $pvalue = GetHttpVars("pvalue", "");
  $taction = GetHttpVars("taction", 0);
  if ($pname != "" && $uid!=-1) {
    $action->parent->param->set($pname, $pvalue, PARAM_USER.$uid, $action->parent->id);
    $action->lay->set("OUT", "");
    $action->parent->session->close();
    syslog(LOG_ERR, "user=[$uid] param=[$pname] value=[$pvalue]");
  } else {
    $action->lay->set("OUT", "Param name unset");
  }
  if ($taction!="") redirect($action, $action->parent->name, $taction);
}
?>
