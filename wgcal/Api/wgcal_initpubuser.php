<?php
include_once('FDL/Lib.Dir.php');
include_once("WGCAL/Lib.Agenda.php");

ini_set("memory_limit","80M");  

global $action;

$rdoc = GetChildDoc($action->GetParam("FREEDOM_DB"), 0, 0, "ALL", array(), 1, "TABLE", "IUSER");
foreach ($rdoc as $ku => $vu) {
  
  echo "Init public agenda for user ".$vu["title"]." (".$vu["id"].") ...";
  setHttpVar("fid", $vu["id"]);
  MonAgenda();
  echo "done\n";

}

?>