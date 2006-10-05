<?php
/**
 * Display two frames
 *
 * @author Anakeen 2003
 * @version $Id: generic_root.php,v 1.5 2006/10/05 09:22:38 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
include_once("GENERIC/generic_util.php");
function generic_root(&$action) {


  $famid=GetHttpVars("famid"); // family restriction 
  $dbaccess = $action->GetParam("FREEDOM_DB");
  if ($famid && (! is_numeric($famid))) $famid=getFamIdFromName($dbaccess,$famid);

  $action->lay->set("famid",$famid);
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
  $action->lay->set("GTITLE",_($action->parent->short_name));
  $action->lay->set("famid",$famid);
}
?>