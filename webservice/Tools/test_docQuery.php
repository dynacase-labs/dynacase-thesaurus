<?php

require_once('Lib.Http.php');
require_once('Lib.Tools.php');

toolhead();

$c = toolinitsoap();

$q = array("id=1085"); // array("title ~ 'portail'");

$param = array( "query" => $q,
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
