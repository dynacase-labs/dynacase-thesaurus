<?php


// refreah for a classname
// use this only if you have changed title attributes

include_once("FDL/Class.Doc.php");

$className = GetHttpVars("class","-"); // classname filter
$famId = GetHttpVars("famid",""); // familly filter

if (($className == "-") && ($famId == 0)) {
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



if ($famId > 0) {
  include_once "FDLGEN/Class.Doc$famId.php";
}
	
  
$query = new QueryDb($dbaccess,"Doc$famId");
$query->AddQuery("locked != -1");

if ($className != "-")$query->AddQuery("classname ~* '$className'");

      
    
$table1 = $query->Query();

     
if ($query->nb > 0)	{
	  while(list($k,$v) = each($table1)) 
	    {	     
	      print $v->title . "-";
	      $v->refresh();
	      $v->Modify();
	      print "\n" ;
	    }	  
}      
    

?>