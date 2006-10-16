<?php
require_once('Lib.Http.php');
require_once('Lib.Tools.php');

toolhead();

$c = toolinitsoap();

$param = array('docid' => 1323);
$r = $c->call('docGetHistory', $param);

toolresult($c, $r);

exit;



?>
