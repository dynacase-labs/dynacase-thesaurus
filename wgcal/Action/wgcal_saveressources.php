<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_saveressources.php,v 1.1 2004/12/03 16:25:12 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

function wgcal_saveressources(&$action) {
  $curress = $action->Read("WGCAL_RESSOURCES");
  if ($curress!="") $action->parent->param->set("WGCAL_U_RESSDISPLAYED", $curress, PARAM_USER.$action->user->id, $action->parent->id);
}

?>