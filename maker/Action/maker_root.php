<?php
/**
 * Project maker
 *
 * @author Anakeen 2008
 * @version $Id: maker_root.php,v 1.1 2008/04/02 11:44:39 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage MAKER
 */
 /**
 */

/**
 * Project maker
 * @param Action &$action current action
 */
function maker_root(&$action) {
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/MAKER/Layout/maker_root.js");
  $action->parent->AddCssRef("FDL:POPUP.CSS",true);
  }
?>