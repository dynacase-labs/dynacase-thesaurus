<?php


// refreah for a classname
// use this only if you have changed title attributes

include_once("FDL/Class.Doc.php");

$famId = GetHttpVars("tofamid",""); // familly filter
$docid = GetHttpVars("docid",""); // document


if (($docid == "") && ($famId == 0)) {
  print "arg class needed :usage --class=<class name> --famid=<familly id>";
  return;
}


$appl = new Application();
$appl->Set("FDL",	   $core);


$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}


$doc= new doc($dbaccess, $docid);
$doc->convert($famId);
print $doc->title. " converted";
    

?>