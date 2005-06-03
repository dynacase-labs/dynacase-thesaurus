<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_prefs_others.php,v 1.5 2005/06/03 05:15:05 marc Exp $
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
		  "mailcc" => array(_("send me event mail copy"), "WGCAL_U_RVMAILCC", "wgcal_hidden", "WGCAL_HIDDEN"),
		  "conflict" => array(_("check for conflicts"), "WGCAL_U_CHECKCONFLICT", "wgcal_hidden", "WGCAL_HIDDEN"),
		  "dispref" => array(_("display refused meetings"), "WGCAL_U_DISPLAYREFUSED", "wgcal_calendar", "WGCAL_CALENDAR"),
		  //"refresh" => array(_("refresh toolbar"), "WGCAL_U_REFRESH_T", "wgcal_toolbar", "WGCAL_TOOLBAR&f=1"),
		  "iconpopup" => array(_("show icons in popup menus"), "WGCAL_U_ICONPOPUP", "wgcal_toolbar", "WGCAL_TOOLBAR")
		  );
  $toolbar = 
    array(
	  //"contacts" => array(_("display contacts"), "WGCAL_U_TBCONTACTS", "wgcal_toolbar", "WGCAL_TOOLBAR"),
	  //"search" => array(_("display search"), "WGCAL_U_TBSEARCH", "wgcal_toolbar", "WGCAL_TOOLBAR"),
	  "todo" => array(_("display todos"), "WGCAL_U_TBTODOS", "wgcal_toolbar", "WGCAL_TOOLBAR")
	  );
  
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
    $io++;
  }
  $action->lay->SetBlockData("TOOLBARTOOLS", $tb);
  return;
}
?>
