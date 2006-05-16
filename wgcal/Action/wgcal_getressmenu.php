<?php
/**
 * Get event producter popup menu
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_getressmenu.php,v 1.2 2006/05/16 17:05:15 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Dir.php");
  include_once("FDL/popupdoc.php");
/**
 * get menu for ressource 
 * @global id Http Var : ressource identificator 
 */
function wgcal_getressmenu(&$action) {

  include_once("WGCAL/Lib.wTools.php");
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $rid = GetHttpVars("rid");

  if ($rid=="")  return _("ressource id not set");
  if (! is_numeric($rid)) $rid=getIdFromName($dbaccess,$rid);
  if (intval($rid) == 0) return _("unknow ressource logical reference");
    
  // A terme 
  //
  // $doc = new_Doc($dbaccess, $rid);
  // if (! $doc->isAffected()) return  sprintf(_("rv cannot see unknow reference %s"),$docid);
  // if (method_exists($doc, "ressAgendaMenu")) $menudesc = $doc->agendaMenu();
  //

  $surl = $action->getParam("CORE_STANDURL");
  $sico = $action->getParam("WGCAL_U_ICONPOPUP", true);

  $menu = array();
  $menu["sub"] = array();
  $menu["main"] = array(
			'displayress' => array( "descr" => _("show/hide this ressource"),
						"jsfunction" => "vuvRessource(".$rid.")",
						"confirm" => "false",
						"tconfirm" => "",
						"control" => "false",
						"target" => "wgcal_hidden",
						"visibility" => POPUP_ACTIVE,
						"icon" => ($sico?$action->getImageUrl("wm-ressshowhide.gif"):""),
						"submenu" => "",
						"barmenu" => "false" ),

			'onlyme' => array( "descr" => _("display only this"),
					   "jsfunction" => "showHideAllRess(".$rid.")",
					   "confirm" => "false",
					   "tconfirm" => "",
					   "control" => "false",
					   "target" => "wgcal_hidden",
					   "visibility" => POPUP_ACTIVE,
					   "icon" => ($sico?$action->getImageUrl("wm-onlyme.gif"):""),
					   "submenu" => "",
					   "barmenu" => "false" ),

			'rcalendar' => array( "descr" => _("view his calendar"),
					      "jsfunction" => "subwindow(450, 700, 'iCalendar', '".$surl."app=WGCAL&action=WGCAL_CALENDAR&sm=1&ress=".$rid."');",
					      "confirm" => "false",
					      "tconfirm" => "",
					      "control" => "false",
					      "target" => "wgcal_hidden",
					      "visibility" => POPUP_ACTIVE,
					      "icon" => ($sico?$action->getImageUrl("mycal.gif"):""),
					      "submenu" => "",
					      "barmenu" => "false" ),

			'rrendezvous' => array( "descr" => _("m_txt_event_new"),
						"jsfunction" => "subwindow(450, 700, 'iNrv', '".$surl."&app=GENERIC&action=GENERIC_EDIT&classid=CALEVENT&wress=".$rid."');",
						"confirm" => "false",
						"tconfirm" => "",
						"control" => "false",
						"target" => "wgcal_hidden",
						"visibility" => ($action->user->fid==$rid?POPUP_INVISIBLE:POPUP_ACTIVE),
						"icon" => ($sico?$action->getImageUrl("wgcal-small.gif"):""),
						"submenu" => "",
						"barmenu" => "false" ),

			'removeress' => array( "descr" => _("remove this ressource"),
					       "jsfunction" => "removeRessource(".$rid.")",
					       "confirm" => "false",
					       "tconfirm" => "",
					       "control" => "false",
					       "target" => "wgcal_hidden",
					       "visibility" => ($action->user->fid==$rid?POPUP_INVISIBLE:POPUP_ACTIVE),
					       "icon" => ($sico?$action->getImageUrl("wm-ressdelete.gif"):""),
					       "submenu" => "",
					       "barmenu" => "false" ),

			'sep1' => array("separator" => true, "visibility" => POPUP_ACTIVE ),

			
			'rprefered' => array( "descr" => _("view my prefered"),
					       "jsfunction" => "subwindow(450, 700, 'iCalendar', '".$surl."app=WGCAL&action=WGCAL_CALENDAR&sm=1&ress=[rplist]')",
					       "confirm" => "false",
					       "tconfirm" => "",
					       "control" => "false",
					       "target" => "wgcal_hidden",
					       "visibility" => POPUP_ACTIVE,
					       "icon" => ($sico?$action->getImageUrl("wm-viewprefered.gif"):""),
					       "submenu" => "",
					       "barmenu" => "false" ),

			'displayallr' => array( "descr" => _("display all ressources"),
						"jsfunction" => "showHideAllRess(0)",
						"confirm" => "false",
						"tconfirm" => "",
						"control" => "false",
						"target" => "wgcal_hidden",
						"visibility" => POPUP_ACTIVE,
						"icon" => ($sico?$action->getImageUrl("wm-ressdisplayall.gif"):""),
						"submenu" => "",
						"barmenu" => "false" ),

			'hideallr' => array( "descr" => _("hide all ressources"),
					     "jsfunction" => "showHideAllRess(-1)",
					     "confirm" => "false",
					     "tconfirm" => "",
					     "control" => "false",
					     "target" => "wgcal_hidden",
					     "visibility" => POPUP_ACTIVE,
					     "icon" => ($sico?$action->getImageUrl("wm-resshideall.gif"):""),
					     "submenu" => "",
					     "barmenu" => "false" ),



			);
  popupdoc($action, $menu["main"], $menu["sub"]);
}
?>
