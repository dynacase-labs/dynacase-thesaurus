<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2003
 * @version $Id: generic_root.php,v 1.1 2003/10/16 09:38:01 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
include_once("GENERIC/generic_util.php");
function generic_root(&$action) {
  // -----------------------------------

  $smode = getSplitMode($action);

  switch ($smode) {
  case "H":  
    
    $action->lay->set("rows",$action->getParam("GENEA_HEIGHT").",*");
    $action->lay->set("cols","");
    break;
  default:
  case "V":

    $action->lay->set("cols",$action->getParam("GENEA_WIDTH").",*");
    $action->lay->set("rows","");
    break;
    
  }
}
?>