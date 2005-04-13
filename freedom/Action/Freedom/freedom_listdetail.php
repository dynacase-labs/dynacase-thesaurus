<?php
/**
 * View folder list with abstract values
 *
 * @author Anakeen 2005
 * @version $Id: freedom_listdetail.php,v 1.1 2005/04/13 11:12:06 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */




include_once("FREEDOM/freedom_view.php");



// -----------------------------------
// -----------------------------------
function freedom_listdetail(&$action) {
// -----------------------------------
  // Set the globals elements


  $action->parent->param->Set("FREEDOM_VIEW","detail",PARAM_USER.$action->user->id,$action->parent->id);

  viewfolder($action, 2);
  


}
?>
