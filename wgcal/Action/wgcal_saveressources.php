<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_saveressources.php,v 1.2 2004/12/07 18:07:07 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

function wgcal_saveressources(&$action) {

  $curress = GetHttpVars("savelist", "");
  if ($curress!="") $action->parent->param->set("WGCAL_U_RESSDISPLAYED", $curress, PARAM_USER.$action->user->id, $action->parent->id);
}

?>