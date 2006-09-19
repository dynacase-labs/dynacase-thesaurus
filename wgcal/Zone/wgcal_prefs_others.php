<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_prefs_others.php,v 1.16 2006/09/19 07:49:33 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once("EXTERNALS/WGCAL_external.php");


function wgcal_prefs_others(&$action) {
  
  $optchk = array(
		  "mailstate" => array(_("attendees change state mail"), "WGCAL_U_MAILCHGSTATE", "wgcal_hidden", "WGCAL_HIDDEN"),
		  "mailcc" => array(_("send me event mail copy"), "WGCAL_U_RVMAILCC", "wgcal_hidden", "WGCAL_HIDDEN"),
		  "conflict" => array(_("check for conflicts"), "WGCAL_U_CHECKCONFLICT", "wgcal_hidden", "WGCAL_HIDDEN"),
		  "dispref" => array(_("display refused meetings"), "WGCAL_U_DISPLAYREFUSED", "wgcal_calendar", "WGCAL_CALENDAR"),
		  "iconpopup" => array(_("show icons in popup menus"), "WGCAL_U_ICONPOPUP", "wgcal_toolbar", "WGCAL_TOOLBAR"),
		  "useicon" => array(_("view icon in event"),"WGCAL_U_RESUMEICON", "wgcal_calendar", "WGCAL_CALENDAR")
		  );
  
  $toolbar = 
    array(
	  "search" => array(_("display search"), "WGCAL_U_TBSEARCH", "wgcal_toolbar", "WGCAL_TOOLBAR")
	  );
  
  $toolbaropt = array( "refresh" => array(_("toolbar refresh time"), 
					  "WGCAL_U_RELOADTOOLBAR", 
					  "wgcal_toolbar", 
					  "WGCAL_TOOLBAR", 
					  array( "0"  => _("never"),
						 "300"   => _("5 minutes"),
						 "600"   => _("10 minutes"),
						 "1200"   => _("20 minutes"))
					  )		       
		       );

  $portal =     array(
		      "look" => array(_("portal look"), 
				      "WGCAL_U_PORTALSTYLE", 
				      "wgcal_hidden", 
				      "WGCAL_HIDDEN", 
				      array( "FIELDSET"  => _("normal"),
					     "TABLE"   => _("condensed"))
				      ),
		      "period" => array(_("displayed period"), 
					"WGCAL_U_PORTALPERIOD", 
					"wgcal_hidden", 
					"WGCAL_HIDDEN", 
					array( "3days"  => _("3days"),
					       "week"   => _("week"),
					       "2weeks"   => _("2weeks"),
					       "month"   => _("month"))
					)
		      );
  $osync = array(
		  "viewdate" => array(_("display last sync date"), "WGCAL_U_OSYNCVDATE", "wgcal_hidden", "WGCAL_HIDDEN")
		  );

  $uid = GetHttpVars("uid", $action->user->id);

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $toptchk = array(); 
  $io = 0;
  foreach ($optchk as $ko => $vo) {
    $toptchk[$io]["idoption"] = $ko;
    $toptchk[$io]["textoption"] = $vo[0];
    $toptchk[$io]["paramoption"] = $vo[1];
    $toptchk[$io]["trefresh"] = $vo[2];
    $toptchk[$io]["arefresh"] = $vo[3];
    $toptchk[$io]["stateoption"] = ($action->GetParam($vo[1]) == 1 ? "checked" : "");
    $toptchk[$io]["uid"] = $uid;
    $io++;
  }
  $action->lay->SetBlockData("MAILOPTCHK", $toptchk);

  $tb = array(); 
  $io = 0;
  foreach ($toolbar as $ko => $vo) {
    $tb[$io]["idoption"] = $ko;
    $tb[$io]["textoption"] = $vo[0];
    $tb[$io]["paramoption"] = $vo[1];
    $tb[$io]["trefresh"] = $vo[2];
    $tb[$io]["arefresh"] = $vo[3];
    $tb[$io]["stateoption"] = ($action->GetParam($vo[1]) == 1 ? "checked" : "");
    $tb[$io]["uid"] = $uid;
    $io++;
  }
  $action->lay->SetBlockData("TOOLBARTOOLS", $tb);

  $toptchk = array(); 
  $io = 0;
  foreach ($toolbaropt as $ko => $vo) {
    $cVal = $action->GetParam($vo[1]);
    $toptchk[$io]["iSel"] = $ko;
    $toptchk[$io]["iText"] = $vo[0];
    $toptchk[$io]["iVar"] = $vo[1];
    $toptchk[$io]["iVal"] = $cVal;
    $toptchk[$io]["iFrame"] = $vo[2];
    $toptchk[$io]["iAction"] = $vo[3];
    $toptchk[$io]["uid"] = $uid;
    $opt = array();
    foreach ($vo[4] as $k => $v) {
      $opt[] = array( "iOptText" => $v,
		      "iOptVal" => $k,
		      "iOptSel" => ($cVal==$k ? "selected" : ""));
    }
    $action->lay->SetBlockData("OPT".$ko, $opt);
    $io++;
  }
  $action->lay->SetBlockData("TOOLBAR2", $toptchk);


  // Portal
  $toptchk = array(); 
  $io = 0;
  foreach ($portal as $ko => $vo) {
    $cVal = $action->GetParam($vo[1]);
    $toptchk[$io]["iSel"] = $ko;
    $toptchk[$io]["iText"] = $vo[0];
    $toptchk[$io]["iVar"] = $vo[1];
    $toptchk[$io]["iVal"] = $cVal;
    $toptchk[$io]["iFrame"] = $vo[2];
    $toptchk[$io]["iAction"] = $vo[3];
    $toptchk[$io]["uid"] = $uid;
    $opt = array();
    foreach ($vo[4] as $k => $v) {
      $opt[] = array( "iOptText" => $v,
		      "iOptVal" => $k,
		      "iOptSel" => ($cVal==$k ? "selected" : ""));
    }
    $action->lay->SetBlockData("OPT".$ko, $opt);
    $io++;
  }
  $action->lay->SetBlockData("PORTAL", $toptchk);

  // Osync
  $toptchk = array(); 
  $io = 0;
  foreach ($osync as $ko => $vo) {
    $toptchk[$io]["idoption"] = $ko;
    $toptchk[$io]["textoption"] = $vo[0];
    $toptchk[$io]["paramoption"] = $vo[1];
    $toptchk[$io]["trefresh"] = $vo[2];
    $toptchk[$io]["arefresh"] = $vo[3];
    $toptchk[$io]["stateoption"] = ($action->GetParam($vo[1]) == 1 ? "checked" : "");
    $toptchk[$io]["uid"] = $uid;
    $io++;
  }
  $action->lay->SetBlockData("OSYNC", $toptchk);


  return;
}
?>
