<?php

//
// ---------------------------------------------------------------

// -----------------------------------
function freedom_frame(&$action) {
// -----------------------------------

  $dirid=GetHttpVars("dirid",0); // root directory
  
  $action->lay->Set("dirid",$dirid);

}
?>
