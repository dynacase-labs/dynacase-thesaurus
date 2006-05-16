<?php


function wgcal_printinfos(&$action) {

  global $_SERVER;
  $action->lay->set("printdate", strftime("%A %d %B %Y %H:%M", time()));
  $action->lay->set("server", $_SERVER["HTTP_HOST"]);

}
?>