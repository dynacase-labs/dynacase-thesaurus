<?php


global $appl,$action;

include_once("FDL/import_file.php");

if (GetHttpVars("htmlmode") == "Y") {
  // mode HTML
  $appl->Set("FREEDOM",	     $core);

  $action->Set("FREEDOM_IMPORT",$appl);


  print ($action->execute());
} else {
  // mode TEXT
  $appl->Set("FDL",	     $core);
  $action->Set("",$appl);
  
  add_import_file($action, 
    		    GetHttpVars("file"));
  
}

    

?>