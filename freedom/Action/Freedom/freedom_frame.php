<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_frame.php,v 1.3 2003/08/18 15:47:03 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */


//
// ---------------------------------------------------------------

// -----------------------------------
function freedom_frame(&$action) {
// -----------------------------------

  $dirid=GetHttpVars("dirid",0); // root directory
  
  $action->lay->Set("dirid",$dirid);

}
?>
