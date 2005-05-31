<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: wgcal_menu.php,v 1.3 2005/05/31 10:27:06 marc Exp $
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

  popupInit("wgcal_m_options", array("download_sync", "close_wgcal_m_options"));
  PopupActive("wgcal_m_options", 0, "download_sync");
  PopupActive("wgcal_m_options", 0, "close_wgcal_m_options");
  popupGen(1);
    
}

?>      
	    