<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_settoolstate.php,v 1.1 2004/11/26 18:52:30 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

include_once("Lib.WGCal.php");

function wgcal_settoolstate(&$action) { 
  $tool = GetHttpVars("tool", CAL_T_NONE);
  if ($tool != CAL_T_NONE) WGCalToolSwitchState($action, $tool);
}

?>