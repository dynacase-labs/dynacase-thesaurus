<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: wgcal_menu.php,v 1.32 2006/05/24 16:04:25 marc Exp $
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

  $action->lay->set("NavDate", ($action->GetParam("WGCAL_U_BARMNAVDATE", 0)==1?true:false));
  $action->lay->set("CmTIME", ($action->GetParam("WGCAL_U_CALCURDATE", time())*1000));
  $action->lay->set("CmTIMEnm", (($action->GetParam("WGCAL_U_CALCURDATE", time()) + (3600*24*31))*1000));
  setToolsLayout($action, 'nav');

  $hdiv   = $action->GetParam("WGCAL_U_HOURDIV", 1);
  for ($h=0; $h<=1; $h++) {
    $tdiv[$h]["value"] = $h+1;
    $tdiv[$h]["descr"] = ($h==0?"1h":"1/".($h+1)."h");
    $tdiv[$h]["selected"] = ($hdiv==$h+1?"selected":"");
  }
  $action->lay->SetBlockData("CHHDIV", $tdiv);

  $setIcons = $action->getParam("WGCAL_U_ICONPOPUP", true);
  $action->lay->set("POPUPICONS", $setIcons);

  $action->lay->set( "wedisplayed",
		     ($action->GetParam("WGCAL_U_VIEWWEEKEND", "yes") == "yes" ? "checked" : ""));
 

  $menus = array( array( "menu"  => "m_event",
			 "label" => _("event menu"),
			 "right" => "WGCAL_USER",
			 "items" => array( array("key" => "m_event_new", 
						 "txt" => _("m_txt_event_new"), 
						 "act" => "[CORE_STANDURL]&app=GENERIC&action=GENERIC_EDIT&classid=CALEVENT",
						 "tgt" => "wgcal_edit", 
						 "ico" => "wgcal-small.gif",
						 "rig" => "WGCAL_USER" 
						 ),
		                       	   array( 
						 "key" => "m_todo_new", 
						 "txt" => _("m_txt_todo_new"), 
						 "act" => "[CORE_STANDURL]&app=WGCAL&action=WGCAL_TODOEDIT",
						 "tgt" => "wgcal_addtodo", 
						 "ico" => "todo-new.gif",
						 "rig" => "WGCAL_USER" 
						 ),
// 		                       	   array( 
// 						 "key" => "m_todo_viewall", 
// 						 "txt" => _("m_txt_todo_viewall"), 
// 						 "act" => "[CORE_STANDURL]&app=WGCAL&action=WGCAL_ALLTODO",
// 						 "tgt" => "wgcal_alltodo", 
// 						 "ico" => "todo-all.gif",
// 						 "rig" => "WGCAL_USER" 
// 						 )
					   )
			 ),
                  array( "menu"  => "m_view",
			 "label" => _("view menu"),
			 "right" => "WGCAL_USER",
			 "items" => array( array( 
						 "key" => "m_view_showhidewe", 
						 "txt" => _("m_txt_view_showhidewe"), 
						 "jsc" => "s=(document.getElementById('wesh').checked? 'no' : 'yes'); mytoto('WGCAL_U_VIEWWEEKEND', s, 'wgcal_calendar', '[CORE_STANDURL]&app=WGCAL&action=WGCAL_CALENDAR');",
						 "ico" => "oneweek.gif",
						 "rig" => "WGCAL_USER"
						 ),
		                       	   array( "key" => "separator" ),
					   array( 
						 "key" => "m_view_week", 
						 "txt" => _("m_txt_view_week"), 
						 "jsc" => "setDaysViewed(7);",
						 "ico" => "oneweek.gif",
						 "rig" => "WGCAL_USER"
						 ),
					   array( 
						 "key" => "m_view_twoweek", 
						 "txt" => _("m_txt_twoview_week"), 
						 "jsc" => "setDaysViewed(14);",
						 "ico" => "twoweek.gif",
						 "rig" => "WGCAL_USER"
						 ),
					   array( 
						 "key" => "m_view_monthtext", 
						 "txt" => _("m_txt_view_monthtext"), 
						 "jsc" => "setDaysViewed(-1);",
						 "ico" => "wm-viewtext.gif",
						 "rig" => "WGCAL_USER"
						 ),
		                       	   array( "key" => "separator" ),
					   array( "key" => "m_weektext", 
						  "txt" => _("m_txt_weektext"), 
						  "act" => "[CORE_STANDURL]&app=WGCAL&action=WGCAL_TEXTWEEK",
						  "tgt" => "wgcal_weektext", 
						  "ico" => "wgcal-weektext.gif",
						  "rig" => "WGCAL_USER",
						  "showmbar" => "true"
						  ),
					   )
			 ),
                  array( "menu"  => "m_calendar",
			 "label" => _("calendar menu"),
			 "right" => "WGCAL_HIDDEN",
			 "items" => array( array( 
						 "key" => "m_calendar_new", 
						 "txt" => _("m_txt_calendar_new"), 
						 "act" => "[CORE_STANDURL]&app=WGCAL&action=WGCAL_CREATECALENDAR",
						 "tgt" => "wgcal_ncalendar", 
						 "ico" => "wgcal-addcalendar.gif",
						 "rig" => "WGCAL_ADMIN"
						 ),
					   array( 
						 "key" => "m_calendar_manage",
						 "txt" => _("m_calendar_manage"), 
						 "act" => "[CORE_STANDURL]&app=WGCAL&action=WGCAL_MNGCALENDAR",
						 "tgt" => "wgcal_mngcalendar",
						 "ico" => "wgcal-mngcalendar.gif",
						 "rig" => "WGCAL_ADMIN"
						 )
					   )
			 ),
                  array( "menu"  => "m_tools",
			 "label" => _("tools menu"),
			 "right" => "WGCAL_USER",
			 "items" => array( 
					  array( 
						"key" => "m_print",
						"txt" => _("m_txt_print"), 
						"jsc" => "subwindowm(450, 700, 'iCalendar', '[CORE_STANDURL]app=WGCAL&action=WGCAL_CALENDAR&sm=1')",
						"ico" => "wm-print.gif",
						"rig" => "WGCAL_USER" 
						),
					  array( 
						 "key" => "m_tools_preferences", 
						 "txt" => _("m_txt_tools_preferences"), 
						 "act" => "[CORE_STANDURL]&app=WGCAL&action=WGCAL_PREFS",
						 "tgt" => "wgcal_editpreferences", 
						 "ico" => "wgcal-prefs.gif",
						 "rig" => "WGCAL_USER"
						 ),
					  array( "key" => "separator", "rig" => "WGCAL_OSYNC" ),
					  array( 
						"key" => "m_conf_synchro",
						"txt" => _("m_txt_confsynch"), 
						"act" => "[CORE_STANDURL]&app=WGCAL&action=WGCAL_OSYNC",
						"tgt" => "wgcal_uploadsync",
						"ico" => "wgcal-osyncinfo.gif",
						"rig" => "WGCAL_OSYNC" 
						),
					  array( 
						"key" => "m_upload_synchronizer",
						"txt" => _("m_txt_synchronizer"), 
						"act" => "[CORE_ABSURL]/osync/synchroniser.zip",
						"tgt" => "wgcal_uploadsync",
						"ico" => "wgcal-osyncdownload.gif",
						"rig" => "WGCAL_OSYNC" 
						),
					  array( "key" => "separator" ),
                                          array(
                                                "key" => "m_help",
                                                "txt" => _("wgcal_help"),
                                                "ico" => "wm-help.gif",
						"act" => "[CORE_STANDURL]app=CORE&action=HELPVIEW&filename=Agenda.pdf",
						"tgt" => "_self",
                                                "rig" => "WGCAL_USER"
                                                ),
					  array( 
						"key" => "m_apropos",
						"txt" => "A propos", 
						"ico" => "wm-apropos.gif",
						"act" => "[CORE_STANDURL]&app=WGCAL&action=WGCAL_APROPOS",
						"tgt" => "wgcal_apropos", 
						"rig" => "WGCAL_USER" 
						)
					  )
			 ),
                  array( "menu"  => "m_admin",
			 "label" => _("administration menu"),
			 "right" => "WGCAL_MGR",
			 "items" => array( array( 
						 "key" => "m_choosecategories", 
						 "txt" => _("m_txt_choosecategories"), 
						 "act" => "[CORE_STANDURL]app=WGCAL&action=WGCAL_CATEDIT",
						 "tgt" => "wgcal_choosecategories", 
						 "ico" => "wgcal_choosecategories.gif",
						 "rig" => "WGCAL_MGR"
						 ),
                                           array(
                                                 "key" => "m_roomscars",
                                                 "txt" => _("m_txt_roomscars"),
                                                 "act" => "[CORE_STANDURL]app=WGCAL&action=WGCAL_ROOMSCARS",
                                                 "tgt" => "wgcal_roomscars",
                                                 "ico" => "vehicle.gif",
                                                 "rig" => "WGCAL_ADMIN"
                                                 ),
// 					   array( "key" => "separator" ),
// 					   array( 
// 						 "key" => "m_userspref", 
// 						 "txt" => _("m_txt_userspref"), 
// 						 "act" => "[CORE_STANDURL]&app=WGCAL&action=WGCAL_PREFS&upref=1",
// 						 "tgt" => "wgcal_userspref", 
// 						 "ico" => "wgcal_userspref.gif",
// 						 "rig" => "WGCAL_ADMIN"
// 						 ),
// 					  array( "key" => "separator" ),
					   array( 
						 "key" => "m_choosegroups", 
						 "txt" => _("m_txt_choosegroups"), 
						 "act" => "[CORE_STANDURL]&app=WGCAL&action=WGCAL_CHOOSEGROUPS",
						 "tgt" => "wgcal_choosegroups", 
						 "ico" => "wgcal_choosegroups.gif",
						 "rig" => "WGCAL_HIDDEN"
						 ),
					   array( 
						 "key" => "m_newtheme",
						 "txt" => _("m_txt_newtheme"), 
						 "ico" => "wm-newtheme.gif",
						 "jsc" => "alert('".addslashes(_("not yet implemented"))."')",
						 "rig" => "WGCAL_HIDDEN" 
						 )
					   )
			 )
		  );
  $mLay = new Layout($action->GetLayoutFile("wgcal_menuitem.xml"),$action);
  $picons = $action->getParam("WGCAL_U_ICONPOPUP", true); 
  $bmenus = array();
  $imenu = 0;
  foreach ($menus as $km => $vm) {

    if (!$action->HasPermission($vm["right"]) || $vm["right"]=="WGCAL_HIDDEN") continue;

    $bmenus[$imenu]["menu"] = $vm["menu"];
    $bmenus[$imenu]["label"] = $vm["label"];
    $bmenus[$imenu]["POPUPICONS"] = $picons;

    $litems = array();
    $items = array();
    $imi = 0;
    foreach ($vm["items"] as $kmi => $vmi) {
      if (!$action->HasPermission($vmi["rig"])) continue;
      if ($vmi["key"] != "separator") $litems[$imi] = $vmi["key"];
      $items[$imi] = $vmi;
      $items[$imi]["SEP"] = ($vmi["key"] == "separator" ? true : false);
      $items[$imi]["menu"] = $vm["menu"];
      $items[$imi]["jscode"] = (isset($vmi["jsc"]) && $vmi["jsc"]!="" ? true : false);
      $items[$imi]["launch"] = (isset($vmi["act"]) && $vmi["act"]!="" ? true : false);
      $items[$imi]["POPUPICONS"] = $picons;
      $items[$imi]["showmbar"] = (isset($vmi["showmbar"]) ? $vmi["showmbar"] : "false");
      $imi++;
    }
    $mLay->SetBlockData("MITEMS", $items);
    $bmenus[$imenu]["MITEM"] =  $mLay->Gen();

    popupInit($vm["menu"], $litems);
    foreach($items as $ki => $vi) {
      if ($action->HasPermission($vi["rig"])) PopupActive($vm["menu"], 1, $vi["key"]);
      else PopupInvisible($vm["menu"], 1, $vi["key"]);
    }
    popupGen(1);
    $imenu++;
  }
  $action->lay->setBlockData("MENU", $bmenus);
  $action->lay->setBlockData("DMENU", $bmenus);


  $action->lay->set("PasIE", ( $action->Read("navigator","")!="EXPLORER" ? true : false )); 
}

?>      
	    
