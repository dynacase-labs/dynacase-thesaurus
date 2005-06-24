<?php
function  wgcal_osync(&$action) {
  global $SERVER_NAME;
  global $SERVER_PORT;
  global $SERVER_ADDR;
  global $REMOTE_ADDR;
  global $REQUEST_URI;
  global $PHP_AUTH_USER;
  global $PHP_AUTH_PW;
  $version = $action->GetParam("WGCAL_SYNCVERSION","0");
  $action->lay->set("server_name", $SERVER_NAME);
  $action->lay->set("server_address", $SERVER_ADDR);
  $action->lay->set("remote_addr", $REMOTE_ADDR);
  $action->lay->set("php_auth_user", $PHP_AUTH_USER);
  $action->lay->set("php_auth_pw", ($PHP_AUTH_PW==""?"[none]":"***********"));
  $action->lay->set("version", $version);
  $action->lay->set("HasPrivilege", ($action->HasPermission("WGCAL_OSYNC")));
  return;
}
