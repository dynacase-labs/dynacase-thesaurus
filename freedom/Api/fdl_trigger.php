<?php


// refreah for a classname
// use this only if you have changed title attributes

include_once("FDL/Lib.Attr.php");




$appl = new Application();
$appl->Set("FDL",	   $core);


$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}



$docid = GetHttpVars("docid",0); // special docid
$trig = (GetHttpVars("trigger","-")!="-"); 

	
$query = new QueryDb($dbaccess,"Doc");
$query->AddQuery("doctype='C'");
  
if ($docid > 0) $query->AddQuery("id=$docid");
      
    
$table1 = $query->Query(0,0,"TABLE");

     
if ($query->nb > 0)	{

  $pubdir = $appl->GetParam("CORE_PUBDIR");

  while(list($k,$v) = each($table1))   {	     
    $doc = createDoc($dbaccess,$v["id"]);
    
    if ($trig)    print $doc->sqltrigger()."\n";
    else {
      if (is_array($doc->sqlcreate)) {
	print implode(";\n",$doc->sqlcreate);
      } else {
	print $doc->sqlcreate."\n";
      }
    }
    
    
  }	 
  
}      
    

?>