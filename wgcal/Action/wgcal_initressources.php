<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_initressources.php,v 1.1 2004/12/03 16:25:12 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

function wgcal_initressources(&$action) {
 $action->Register("WGCAL_RESSOURCES", "");
 redirect($action, $action->parent->name, "WGCAL_TOOLBAR");
}

?>