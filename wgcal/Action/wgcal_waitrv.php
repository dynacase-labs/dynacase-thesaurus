<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_waitrv.php,v 1.12 2006/04/28 14:42:52 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

include_once("FDL/Class.Doc.php");
include_once('FDL/Lib.Dir.php');
include_once('Lib.wTools.php');
include_once("WGCAL/Lib.wTools.php");
include_once("EXTERNALS/WGCAL_external.php");


function wgcal_waitrv(&$action) {

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef("WHAT/Layout/AnchorPosition.js");
  $action->parent->AddJsRef("WHAT/Layout/geometry.js");
  $action->parent->AddJsRef("WHAT/Layout/DHTMLapi.js");
  $action->parent->AddJsRef("FDL/Layout/popupdoc.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_calendar.js");
  $action->parent->AddCssRef("FDL:POPUP.CSS",true);

  $oapp = GetHttpVars("oapp", "WGCAL");
  $oact = GetHttpVars("oact", "WGCAL_CALENDAR");
  $action->lay->set("oapp", $oapp);
  $action->lay->set("oact", $oact);
  $intoolbar = (GetHttpVars("tb", "N")=="Y"?true:false);
  $action->lay->set("intoolbar", $intoolbar);

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_waitzone.js");

  $rvtextl =  25;
  $today = w_datets2db(time(), false)." 00:00:00";
  $filter[] = "(calev_end > '".$today."' ) AND (calev_attid ~* '".$action->user->fid."')";
  $rdoc = GetChildDoc($dbaccess, 0, 0, "ALL", $filter, 
		      $action->user->id, "TABLE", getIdFromName($dbaccess,"CALEVENT"));

  $irv = 0;
  foreach ($rdoc as $k => $v)  {
    $doc = getDocObject($action->GetParam("FREEDOM_DB"), $v);
    $attid = $doc->getTValue("calev_attid");
    $attst = $doc->getTValue("calev_attstate");
    $state = -1;
    foreach ($attid as $ka => $va) {
      if ($va==$action->user->fid && ($attst[$ka]==EVST_NEW||$attst[$ka]==EVST_READ||$attst[$ka]==EVST_TBC)) $state = $attst[$ka]; 
    }
    if ($state!=-1) {
      $wrv[$irv] = array ( "id" => $v["id"],
			   "date" =>  substr($v["calev_start"],0,16),
 			   "title" => $v["calev_evtitle"],
			   "owner" => ucwords(strtolower($v["calev_owner"])) );
      $irv++;
    }
  }

  if (!$intoolbar) {
    $alertfornewevent = $action->GetParam("WGCAL_U_WRVALERT", 1);
    $action->lay->set("alertwrv", "checked");
    if ($alertfornewevent == 0) $action->lay->set("alertwrv", "");
  }

  if (count($wrv)>0) {
    $action->lay->set("RVCOUNT", count($wrv));
    $action->lay->SetBlockData("WAITRV", $wrv);
    if (!$intoolbar && $alertfornewevent>0) AddWarningMsg(_("You have waiting events").". (".count($wrv).")"); 
  } else {
    $action->lay->SetBlockData("WAITRV", null);
    $action->lay->set("RVCOUNT", "0");
  }

  setToolsLayout($action, 'waitrv', ($intoolbar?false:true));

  
}

?>
