<?php
/**
 * redirect to generic
 *
 * @author Anakeen 2003
 * @version $Id: onefam_genroot.php,v 1.1 2004/08/23 13:44:44 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
include_once("GENERIC/generic_root.php");
function onefam_genroot(&$action) {
  // -----------------------------------
  generic_root($action);
  if ($action->HasPermission("ONEFAM_MASTER")) {
    $action->lay->setBlockData("ADMIN",array(array("zou")));
    $action->lay->set("adminrows","30pt,*");
  } else {
    $action->lay->set("adminrows","*");
  }
}
?>