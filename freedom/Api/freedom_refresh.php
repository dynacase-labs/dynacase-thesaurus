<?php


// refreah for a classname
// use this only if you have changed title attributes

include_once("FDL/Class.Doc.php");

$famId = GetHttpVars("famid",""); // familly filter


if  ($famId == 0) {
  print "arg class needed :usage  --famid=<familly id>";
  return;
}


$appl = new Application();
$appl->Set("FDL",	   $core);


$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}



if ($famId > 0) {
  include_once "FDLGEN/Class.Doc$famId.php";
}
	
  
$query = new QueryDb($dbaccess,"Doc$famId");
$query->AddQuery("locked != -1");


      
    
$table1 = $query->Query(0,0,"TABLE");

     
if ($query->nb > 0)	{

  printf("\n%d documents to refresh\n", count($table1));
  $card=count($table1);
  $doc = createDoc($dbaccess,$famId,false);
	  while(list($k,$v) = each($table1)) 
	    {	     
	      $doc->Affect($v);
	      print $card-$k.")".$doc->title . "\n";
	      $doc->refresh();
	      $doc->refreshTitle();
	      $doc->Modify();

	    }	  
}      
    

?>