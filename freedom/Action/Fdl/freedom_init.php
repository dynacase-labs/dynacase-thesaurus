<?php

include_once("FDL/import_file.php");

// -----------------------------------
function freedom_init(&$action) {
  // -----------------------------------
add_import_file($action, 
    		    $action->GetParam("CORE_PUBDIR")."/FDL/init.freedom");
    
}
?>
