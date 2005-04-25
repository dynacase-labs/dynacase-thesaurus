<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: contacts.php,v 1.1 2005/04/25 19:03:17 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

function contacts(&$action) {
  $themef = $action->getParam("WGCAL_U_THEME", "default");
  if (file_exists("WGCAL/Themes/$themef.thm"))     include_once("WGCAL/Themes/$themef.thm");
  else    include_once("WGCAL/Themes/default.thm");
  $action->lay->set("toolbarwidth", ($theme->WTH_TOOLBARW-4));
}
?>
