<?php
/**
 * Import document descriptions
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_import.php,v 1.8 2004/03/16 14:12:46 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */


include_once("FDL/import_file.php");





// -----------------------------------
function freedom_import(&$action) {
  // -----------------------------------
  $nbdoc=add_import_file(&$action); 

  $action->lay->Set("nbdoc","$nbdoc");
}



?>
