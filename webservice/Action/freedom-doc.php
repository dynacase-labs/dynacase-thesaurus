<?php
require_once('nusoap.php');
require_once('Lib.FreedomWSUtils.php');
require_once('Lib.FreedomWSDoc.php');
require_once('Lib.FreedomWSUser.php');

ini_set("display_errors", "0");
 
$s = new soap_server("Wsdl/freedom-doc.wsdl");

$s->service($HTTP_RAW_POST_DATA);
if ($s->fault) fwsLog("Server error", "E", __FILE__,__LINE__);


exit;
?>
