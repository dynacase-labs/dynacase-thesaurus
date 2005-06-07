<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: wgcal_menu.php,v 1.5 2005/06/07 16:05:36 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage
 */
 /**
 */
include_once("WGCAL/Lib.WGCal.php");
include_once("EXTERNALS/WGCAL_external.php");

function wgcal_menu(&$action) {
  include_once("FDL/popup_util.php");

  $setIcons = $action->getParam("WGCAL_U_ICONPOPUP", true);
  $action->lay->set("POPUPICONS", $setIcons);

  $action->lay->set( "wedisplayed",
		     ($action->GetParam("WGCAL_U_VIEWWEEKEND", "yes") == "yes" ? "checked" : ""));
  

  popupInit("wgcal_m_options", array("download_sync", "close_wgcal_m_options"));
  PopupActive("wgcal_m_options", 0, "download_sync");
  PopupActive("wgcal_m_options", 0, "close_wgcal_m_options");
  popupGen(1);

  $action->lay->set("PasIE", ( $action->Read("navigator","")!="EXPLORER" ? true : false )); 
}

?>      
	    