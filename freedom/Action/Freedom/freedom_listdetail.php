<?php
/**
 * View folder list with abstract values
 *
 * @author Anakeen 2005
 * @version $Id: freedom_listdetail.php,v 1.2 2005/08/18 09:16:09 eric Exp $
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


  $memo=(getHttpVars("memo","N")=="Y");
  

  if ($memo) $action->parent->param->Set("FREEDOM_VIEW","detail",PARAM_USER.$action->user->id,$action->parent->id);

  viewfolder($action, 2);
  


}
?>
