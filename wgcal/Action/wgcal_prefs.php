<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_prefs.php,v 1.6 2005/08/19 17:21:33 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once("EXTERNALS/WGCAL_external.php");


function wgcal_prefs(&$action) {

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/DHTMLapi.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/PopupWindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/ColorPicker2.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_prefs.js");

  $userspref = GetHttpVars("upref", 0);
  $userid = GetHttpVars("uid", $action->user->id);
  if ($userspref>0) {
    $action->lay->set("ShowUsers", true);
    $tusers = array(); $nu = 0;
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $families = getFamIdFromName($dbaccess, "IUSER");

    $rdoc = GetChildDoc($dbaccess, 0, 0, "ALL", array(), $action->user->id, "TABLE", $families);
    foreach ($rdoc as $k => $v) {
      $tusers[$nu]["uid"] = $v["us_whatid"];
      $tusers[$nu]["utext"] = ucwords(strtolower($v["title"]));
      $tusers[$nu]["uselected"] = ($v["us_whatid"] == $userid ? "selected" : "");
      $nu++;
    } 
    $action->lay->SetBlockData("Users", $tusers);
  } else {
    $action->lay->set("ShowUsers", false);
  }

  $Zone = array( "look" => array(N_("look preferences"),"WGCAL_USER"), 
		 "contacts" => array(N_("my prefered contacts"),"WGCAL_USER"), 
		 "todopref" => array(N_("todo preferences"),"WGCAL_USER"), 
		 "vcal" => array(N_("agenda visibility"),"WGCAL_VCAL"), 
		 "others" => array(N_("other preferences"),"WGCAL_USER"));

  $tz = array();
  $itz = 0;
  foreach ($Zone as $kz => $vz) {
    if ($action->HasPermission($vz[1])) {
      if ($kz=="contacts" && $userspref!=0) continue;
      $tz[$itz]["izone"] = $itz;
      $tz[$itz]["tzone"] = $kz;
      $tz[$itz]["dzone"] = $vz[0];
      $tz[$itz]["azone"] = strtoupper($kz);
      $tz[$itz]["vzone"] = ($itz==0?"":"none");
      $tz[$itz]["uid"] = $userid;
      $itz++;
    }
  }
  $action->lay->SetBlockData("ZoneJS", $tz);
  $action->lay->SetBlockData("ZoneIcons", $tz);
  $action->lay->SetBlockData("ZoneXml", $tz);
   
  return;
}
?>
