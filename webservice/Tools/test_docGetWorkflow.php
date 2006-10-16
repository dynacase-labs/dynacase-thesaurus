<?php

require_once('/usr/share/nusoap/nusoap.php');
require_once('Lib.Http.php');
require_once('Lib.Tools.php');

toolhead();

$c = toolinitsoap();

$param = array('docid' => 1391);
$r = $c->call('docGetWorkflow', $param);

toolresult($c, $r);

exit;


?>
