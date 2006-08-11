<?php

function fgetmsg(&$action) {
  $mtype = GetHttpVars("mtype", "I");
  $remove = GetHttpVars("rm", "N");
  $wm = $action->parent->GetWarningMsg();
  $d = "";
  if (is_array($wm) && count($wm)>0) {
    foreach ($wm as $k => $v) {
      $d .= "<div>".htmlentities($v)."</div>";
    }
  }
  if ($remove=="Y") $action->parent->ClearWarningMsg();
  $action->lay->set("OUT", $d);
  return ;
}
?>