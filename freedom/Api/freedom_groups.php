<?php


// refreah for a classname
// use this only if you have changed title attributes

include_once("FDL/Class.Doc.php");



$appl = new Application();
$appl->Set("FDL",	   $core);


$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}

$doc = new Doc($dbaccess);

system("echo 'drop table groups;delete from docperm where upacl=0 and unacl=0;update docperm set cacl=0' | psql freedom anakeen");
system("pg_dump -t groups anakeen -U anakeen | psql freedom anakeen");


?>