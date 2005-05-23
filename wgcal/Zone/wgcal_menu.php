<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: wgcal_menu.php,v 1.1 2005/05/23 16:19:03 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage
 */
 /**
 */
include_once("WGCAL/Lib.WGCal.php");
include_once("WGCAL/WGCAL_external.php");

function wgcal_menu(&$action) {
  include_once("FDL/popup_util.php");


  // id menu
  $moptions = array(
		  array( "item_id"      => "download_sync", 
			 "item_access"  => "WGCAL_USER",
			 "item_onclick" => $action->getParam("CORE_ABSURL")."/".$action->parent->name."/synchronzer.zip",
			 "item_icon"    => $action->getImageUrl("syncro.gif"),
			 "item_label"   => _("Outlook synchronizer") )
		  );
  $optid = array();
  foreach ($moptions as $k => $v) {
    $optid[] = $v["item_id"];
    $action->lay->set("POPUPICONS", $action->getParam("WGCAL_U_ICONPOPUP", true));
    $moptions[$k]["POPUPICONS"] = $action->getParam("WGCAL_U_ICONPOPUP", true);
  }
//   print_r2($moptions);
  $action->lay->setBlockData("OPTIONS_MENU", $moptions);
//   print_r2($optid);
  popupInit('wgcal_m_options', $optid);
  foreach ($optid as $k => $v) {
    PopupActive('wgcal_m_options', 0, $v);
  }
  popupGen(1);
    
}

?>      
	    