<?php


// remove all tempory doc and orphelines values
include_once("FDL/Class.Doc.php");


$appl = new Application();
$appl->Set("FDL",	   $core);


$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}
$dbid= pg_connect($dbaccess);
$res= pg_exec($dbid,"delete from doc where doctype='T'");
if (!$res) pg_errormessage($dbid);
$res= pg_exec($dbid,"delete from docvalue where docid not in (select id from doc);");
if (!$res) pg_errormessage($dbid);
$res= pg_exec($dbid,"delete from docattr where docid not in (select id from doc); ");
if (!$res) pg_errormessage($dbid);

$res= pg_exec($dbid,"delete from fld where dirid not in (select id from doc); ");
if (!$res) pg_errormessage($dbid);

$res= pg_exec($dbid,"delete from fld where childid not in (select id from doc); ");
if (!$res) pg_errormessage($dbid);

pg_close($dbid);
    

?>