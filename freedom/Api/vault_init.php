<?php
 
include_once("VAULT/Class.VaultFile.php");
   
$appl = new Application();
$appl->Set("FDL",	   $core);

$dbaccess=$appl->GetParam("FREEDOM_DB");

$vf = new VaultFile($dbaccess, "FREEDOM");
?>