<?php
require_once('Lib.Http.php');
require_once('Lib.Tools.php');

toolhead();

$c = toolinitsoap();

// $param = array('docid' => 1323, 'docrev' => '1');
$param = array('docid' => 1187, 'docrev' => '');
$r = $c->call('docRead', $param);

toolresult($c, $r);

exit;



?>
