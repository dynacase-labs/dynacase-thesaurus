<?php


function wgcal_histo(&$action) {

  $ev = GetHttpVars("ev", -1);
  $action->lay->set("ev", $ev);

}