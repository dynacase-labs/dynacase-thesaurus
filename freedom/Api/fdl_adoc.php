<?php


// refreah for a classname
// use this only if you have changed title attributes

include_once("FDL/Lib.Attr.php");
include_once("FDL/Class.DocFam.php");




$appl = new Application();
$appl->Set("FDL",	   $core);


$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}



$docid = GetHttpVars("docid",0); // special docid


	
$query = new QueryDb($dbaccess,"DocFam");
$query ->AddQuery("doctype='C'");
$query->order_by="fromid";

  
if ($docid > 0) $query->AddQuery("id=$docid");
      
    
$table1 = $query->Query(0,0,"TABLE");

     
if ($query->nb > 0)	{

  $pubdir = $appl->GetParam("CORE_PUBDIR");

  while(list($k,$v) = each($table1))   {	     
    //    print AttrtoPhp($dbaccess,$v->id);
    print "$pubdir/FDLGEN/Class.Doc".$v["id"].".php\n";
    createDocFile($dbaccess,$v);
    
    $msg=PgUpdateFamilly($dbaccess, $v["id"]);
    print $msg;
    
  }	 
  
}      
    

?>