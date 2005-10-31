<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: onefam_root.php,v 1.5 2005/10/31 15:09:01 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
include_once("GENERIC/generic_util.php");
function onefam_root(&$action) {
  // -----------------------------------

  $nbcol=intval($action->getParam("ONEFAM_LWIDTH",1));

  $delta=20;
  if ($action->read("navigator") == "EXPLORER") $delta=50;
  
  $iz=$action->getParam("CORE_ICONSIZE");
  if ($iz == "small") {
    $izpx=42;
  } else {
    $izpx=56;	
  }
  $action->lay->set("wcols",$izpx*$nbcol+$delta);
  $action->lay->set("Title",_($action->parent->short_name));
 
  
  $openfam=$action->getParam("ONEFAM_FAMOPEN");
  if ($openfam > 0) {

    $action->lay->set("OPENFAM",true);
    $action->lay->set("openfam",$openfam);
  } else {
    $action->lay->set("OPENFAM",false);
  }
}
?>