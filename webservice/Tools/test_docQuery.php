<?php

require_once('/usr/share/nusoap/nusoap.php');
require_once('Lib.Http.php');
require_once('Lib.Tools.php');

toolhead();

$c = toolinitsoap();

$param = array( "query" => array("title ~ 'portail'"),
	       "start"=>0, 
	       "slice" => 20, 
	       "famid" => "USER_PORTAL", 
		"state" => "",
	       "allrev" => false, 
	       "trash" => false, 
	       "orderby" => "title" );
$r = $c->call('docQuery', $param);

toolresult($c, $r);

exit;

?>
