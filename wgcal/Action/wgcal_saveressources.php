<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_saveressources.php,v 1.3 2004/12/09 17:30:17 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

function wgcal_saveressources(&$action) {
  $curress = GetHttpVars("savelist", "");
  $action->parent->param->set("WGCAL_U_RESSDISPLAYED", $curress, PARAM_USER.$action->user->id, $action->parent->id);
}

?>