<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_htmlhead.php,v 1.2 2005/02/17 17:25:29 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// $Id: wgcal_htmlhead.php,v 1.2 2005/02/17 17:25:29 marc Exp $


include_once('Class.QueryDb.php');
include_once('Class.Application.php');

function wgcal_htmlhead(&$action) {
  $theme = $action->getParam("WGCAL_U_THEME", "default");
  $action->lay->set("theme", $theme);	
}
?>
