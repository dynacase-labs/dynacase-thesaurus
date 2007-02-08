<?php
require_once('Lib.Http.php');
require_once('Lib.Tools.php');

toolhead();

$c = toolinitsoap("http://localhost/freedomWS","admin","anakeen");

// $param = array('docid' => 1323, 'docrev' => '1');
/*
$param = array('login' => 'eric');
$r = $c->call('getAvailableApplication', $param);

if ($r) {
  print ($c->response);
  print "<hr><pre>";
  print_r(unserialize($r));
  print "</pre>";
 } else {
toolresult($c, $r);
 }
*/
$param = array('login' => 'jean.groget',"password"=>'coucou');
$r = $c->call('changePassword', $param);

if ($r) {
  print ($c->response);
  print "<hr><pre>";
  print_r(unserialize($r));
  print "</pre>";
 } else {
toolresult($c, $r);
 }

exit;



?>
