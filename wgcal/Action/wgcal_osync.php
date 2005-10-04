<?php
function  wgcal_osync(&$action) {
  global $_SERVER;
  $version = $action->GetParam("WGCAL_SYNCVERSION","0");
  $action->lay->set("server_name", $_SERVER["SERVER_NAME"]);
  $action->lay->set("server_address", $_SERVER["SERVER_ADDR"]);
  $action->lay->set("remote_addr", $_SERVER["REMOTE_ADDR"]);
  $action->lay->set("php_auth_user", $_SERVER["PHP_AUTH_USER"]);
  $action->lay->set("php_auth_pw", ($_SERVER["PHP_AUTH_PW"]==""?"[none]":"***********"));
  $action->lay->set("version", $version);
  $action->lay->set("HasPrivilege", ($action->HasPermission("WGCAL_OSYNC")));
  return;
}
?>
