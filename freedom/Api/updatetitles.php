<?php


// update title for a classname
// use this only if you have changed title attributes

include_once("FDL/Class.Doc.php");

$className = GetHttpVars("class","-"); // output file
if ($className == "-") {
  print "arg class needed :usage --class=<class name>";
  return;
}

$appl = new Application();
$appl->Set("FDL",	   $core);


$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}




	
  
$query = new QueryDb($dbaccess,"Doc");
$query->AddQuery("classname ~* '$className'");

      
    
$table1 = $query->Query();

     
if ($query->nb > 0)	{
	  while(list($k,$v) = each($table1)) 
	    {	     
	      print $v->title . "-";
	      $v->refreshTitle();
	      $v->Modify();
	      print $v->title."\n" ;
	    }	  
}      
    

?>