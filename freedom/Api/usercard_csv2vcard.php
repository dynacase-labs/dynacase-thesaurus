<?php

// remove all tempory doc and orphelines values
include_once("FDL/Class.DocUser.php");
include_once("FDL/Class.UsercardVcard.php");


$fimport = GetHttpVars("ifile"); // file to convert
$fvcf = GetHttpVars("ofile","php://stdin"); // output file
$appl = new Application();
$appl->Set("USERCARD",	   $core);


$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}



  $doc = new DocUser($dbaccess,$action->GetParam("IDFAMUSER", FAM_DOCUSER));

  $lattr = $doc->GetAttributes();
  $format = "DOC;".$doc->id.";<special id>;<special dirid>; ";

  while (list($k, $attr) = each ($lattr)) {
    $format .= $attr->labeltext." ;";
  }

//print_r( $lattr);;

$usercard = new UsercardVcard();

 $fdoc = fopen($fimport,"r");

$deffam = $action->GetParam("IDFAMUSER", FAM_DOCUSER);

 $usercard->open($fvcf,"w");
  while ($data = fgetcsv ($fdoc, 1000, ";")) {    
    $num = count ($data);
    if ($data[0] != "DOC") continue;
    if ($data[1] != $deffam) continue;
   

    $attr = array();
    reset($data);
    //array_shift($data);array_shift($data);array_shift($data);array_shift($data);
    while (list($k,$v)= each($data)) {
      if ($k > 3) $attr[$lattr[$k-4]->id]=$v;
    }

    $usercard->WriteCard($attr[QA_LNAME]." ".$attr[QA_FNAME], $attr);
  }
?>