<?php

include_once("FREEDOM/freedom_import.php");

// -----------------------------------
function freedom_init(&$action) {
  // -----------------------------------
add_import_file($action, 
    		    $action->GetParam("CORE_PUBDIR")."/FREEDOM/init.freedom");
    
}
?>