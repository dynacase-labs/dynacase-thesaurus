<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_settoolstate.php,v 1.2 2004/12/08 16:43:52 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

include_once("Lib.WGCal.php");

function wgcal_settoolstate(&$action) { 
  $toolsvis = GetHttpVars("toolsvis", "");
  $action->parent->param->set("WGCAL_U_TOOLSSTATE", $toolsvis, PARAM_USER.$action->user->id, $action->parent->id);
}

?>
