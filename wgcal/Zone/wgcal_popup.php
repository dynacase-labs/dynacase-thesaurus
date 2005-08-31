<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: wgcal_popup.php,v 1.5 2005/08/31 16:59:41 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage
 */
 /**
 */
include_once("WGCAL/Lib.WGCal.php");
include_once("WGCAL/Lib.wTools.php");
include_once("EXTERNALS/WGCAL_external.php");

function wgcal_popup(&$action) {
  include_once("FDL/popup_util.php");
  $action->lay->Set("ISPOPUPITEMS", false);
  $ev = GetHttpVars("ev", -1);
  if ($ev>0) {
    $action->lay->set("id", $ev);
    $n = new Doc($dbaccess, $ev);
    $t = array();
    if (isset($n->calPopupMenu) && is_array($n->calPopupMenu)) {
      foreach($n->calPopupMenu as $k => $v) {
	$t[]["item"] = $k;
	$t[]["label"] = $action->getText($v["label"]);
	$t[]["app"] = $v["app"];
	$t[]["action"] = $v["action"];
	$p = "";
	foreach($v["params"] as $kp => $vp) {
	  $p .= "&".$kp."=".$vp;
	}
	$t[]["params"] = $p;
	$t[][""] = $v[""];
      }
      $action->lay->SetBlockData("POPUPITEMS", $t);
      $action->lay->Set("ISPOPUPITEMS", true);
    } 
  }
  $action->lay->set("POPUPICONS", $action->getParam("WGCAL_U_ICONPOPUP", true));
  $action->lay->set("DebugMode", wDebugMode());
}


?>