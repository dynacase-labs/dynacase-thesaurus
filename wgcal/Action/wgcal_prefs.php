<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_prefs.php,v 1.3 2005/03/30 10:04:40 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once("WGCAL/WGCAL_external.php");


function wgcal_prefs(&$action) {

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/DHTMLapi.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/PopupWindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/ColorPicker2.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_prefs.js");

  $Zone = array( "look" => N_("look preferences"), 
		 "contacts" => N_("my prefered contacts"), 
		 "todopref" => N_("todo preferences"), 
		 "others" => N_("other preferences"));

  $tz = array();
  $itz = 0;
  foreach ($Zone as $kz => $vz) {
    $tz[$itz]["izone"] = $itz;
    $tz[$itz]["tzone"] = $kz;
    $tz[$itz]["dzone"] = $vz;
    $tz[$itz]["azone"] = strtoupper($kz);
    $tz[$itz]["vzone"] = ($itz==0?"":"none");
    $itz++;
  }
  $action->lay->SetBlockData("ZoneJS", $tz);
  $action->lay->SetBlockData("ZoneIcons", $tz);
  $action->lay->SetBlockData("ZoneXml", $tz);
   
  return;
}
?>
