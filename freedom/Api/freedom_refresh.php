<?php


// refreah for a classname
// use this only if you have changed title attributes

include_once("FDL/Class.Doc.php");

$className = GetHttpVars("class","-"); // output file
if ($className == "-") {
  print "arg class needed :usage --class=<class name> --famid=<familly id>";
  return;
}

$famId = GetHttpVars("famid",0); // output file

$appl = new Application();
$appl->Set("FDL",	   $core);


$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}




	
  
$query = new QueryDb($dbaccess,"Doc");
$query->AddQuery("locked != -1");
$query->AddQuery("classname ~* '$className'");
if ($famId > 0) $query->AddQuery("fromid = $famId");
      
    
$table1 = $query->Query();

     
if ($query->nb > 0)	{
	  while(list($k,$v) = each($table1)) 
	    {	     
	      print $v->title . "-";
	      $v->refresh();
	      $v->Modify();
	      print $v->title."\n" ;
	    }	  
}      
    

?>