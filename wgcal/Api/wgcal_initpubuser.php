<?php
include_once('FDL/Lib.Dir.php');
include_once("WGCAL/Lib.Agenda.php");

ini_set("memory_limit","80M");  

global $action;

$ulogin=GetHttpVars("ulogin", "");
$ufid=GetHttpVars("ufid", 0);

$filter=array();
if ($ulogin!="") $filter[] = "(us_login = '".$ulogin."')";
 else if ($ufid>0) $filter[] = "(id = '".$ufid."')";

$rdoc = GetChildDoc($action->GetParam("FREEDOM_DB"), 0, 0, "ALL", $filter, 1, "TABLE", "IUSER");
if (count($rdoc)>0) {
  foreach ($rdoc as $ku => $vu) {  
    echo "Init public agenda for user ".$vu["title"]." (".$vu["id"].") ...";
    setHttpVar("fid", $vu["id"]);
    MonAgenda();
    echo "done\n";
  }
 }
exit;

?>