<?php
/**
 * Project maker
 *
 * @author Anakeen 2008
 * @version $Id: maker_root.php,v 1.4 2008/04/18 15:18:43 eric Exp $
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
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/WHAT/Layout/prototype.js");
  //  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/MAKER/Layout/maker_root.js");
  $action->parent->AddJsRef("MAKER:maker_root.js",true);
  
  $action->parent->AddCssRef("FDL:POPUP.CSS",true);
  $action->parent->AddCssRef("MAKER:maker_root.css",true);
  $action->parent->AddCssRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/prototree.css");
  }
?>