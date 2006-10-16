<?php
require_once('Lib.Tools.php');

toolhead();

$c = toolinitsoap();

$param = array();
$r = $c->call('docAPIVersion', $param);

toolresult($c, $r);

exit;


?>
