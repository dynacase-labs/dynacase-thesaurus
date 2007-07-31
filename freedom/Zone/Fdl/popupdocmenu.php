<?php
/**
 * Specific menu for family
 *
 * @author Anakeen 2000 
 * @version $Id: popupdocmenu.php,v 1.3 2007/07/31 13:49:44 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/popupdoc.php");
// -----------------------------------
function popupdocmenu(&$action) {
  // -----------------------------------
  // define accessibility
  $docid = GetHttpVars("id");
  $abstract = (GetHttpVars("abstract",'N') == "Y");
  $zone = GetHttpVars("zone"); // special zone

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new_Doc($dbaccess, $docid); # _("States")

  if ($zone=="") $specmenu=$doc->specialmenu;
  else $specmenu=$zone;
  if (ereg("(.*):(.*)",$specmenu,$reg)) {
    $menuapp=$reg[1];
    $menuaction=$reg[2];
  } else {
    $menuapp="FDL";
    $menuaction="POPUPDOCDETAIL";    
  }

  $action->lay->set("menuapp",$menuapp);
  $action->lay->set("menuaction",$menuaction);
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/popupdoc.js");
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/popupdocmenu.js");
  $action->parent->AddCssRef("FDL:POPUP.CSS",true);

}

?>