<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: onefam_root.php,v 1.1 2003/10/16 09:38:01 eric Exp $
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
  
  $action->lay->set("wcols",54*$nbcol+$delta);
 
}
?>