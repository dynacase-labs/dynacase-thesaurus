<?php


// remove all tempory doc and orphelines values
global $appl,$action;

include_once("FDL/import_file.php");
$appl->Set("FDL",	     $core);

$action->Set("",$appl);


add_import_file($action, 
    		    GetHttpVars("file"));

    

?>