<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_waitrv.php,v 1.10 2006/04/04 04:06:38 marc Exp $
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

  $oapp = GetHttpVars("oapp", "WGCAL");
  $oact = GetHttpVars("oact", "WGCAL_CALENDAR");
  $action->lay->set("oapp", $oapp);
  $action->lay->set("oact", $oact);
  $intoolbar = (GetHttpVars("tb", "N")=="Y"?true:false);
  $action->lay->set("intoolbar", $intoolbar);

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_waitzone.js");

  $rvtextl =  30;
  $today = w_datets2db(time()-(24*3600*7), false)." 00:00:00";
  $filter[] = "(calev_start > '".$today."' ) AND (calev_attid ~* '".$action->user->fid."')";
  $irv = count($wrv);
  $rdoc = GetChildDoc($dbaccess, 0, 0, "ALL", $filter, 
		      $action->user->id, "TABLE", getIdFromName($dbaccess,"CALEVENT"));

  foreach ($rdoc as $k => $v)  {
    $doc = new_Doc($action->GetParam("FREEDOM_DB"), $v["id"]);
    $attid = $doc->getTValue("CALEV_ATTID");
    $attst = $doc->getTValue("CALEV_ATTSTATE");
    $state = -1;
    foreach ($attid as $ka => $va) {
      if ($va==$action->user->fid && ($attst[$ka]==EVST_NEW||$attst[$ka]==EVST_READ||$attst[$ka]==EVST_TBC)) $state = $attst[$ka]; 
    }
    if ($state != -1) {
      $label = WGCalGetLabelState($state); 
      $wrv[$irv]["wrvfontstyle"] = ""; 
      $wrv[$irv]["wrvcolor"] = WGCalGetColorState($state); 
      $wrv[$irv]["wrvid"] = $v["id"];
      $wrv[$irv]["wrvtitlejs"] = addslashes($v["calev_evtitle"]);
     if (strlen($v["calev_evtitle"])>$rvtextl) $wrv[$irv]["wrvtitle"] = substr($v["calev_evtitle"],0,$rvtextl)."...";
      else $wrv[$irv]["wrvtitle"] = $v["calev_evtitle"];
      $wrv[$irv]["wrvicon"] = $doc->GetIcon($v["icon"]);
      $wrv[$irv]["tsdate"] = w_dbdate2ts($v["calev_start"]);
      $wrv[$irv]["owner"] = ucwords(strtolower($v["calev_owner"]));
      $irv++;
    }
  }

  if (!$intoolbar) {
    $alertfornewevent = $action->GetParam("WGCAL_U_WRVALERT", 1);
    $action->lay->set("alertwrv", "checked");
    if ($alertfornewevent == 0) $action->lay->set("alertwrv", "");
  }

  if (count($wrv)>0) {
    // Init popup
    include_once("FDL/popup_util.php");
    popupInit('waitpopup',  array('acceptevent',  'refuseevent', 'viewevent', 'gotoperiod'));
    foreach ($wrv as $k => $v) {
      PopupActive('waitpopup', $k, 'acceptevent');
      PopupActive('waitpopup', $k, 'refuseevent');
      PopupActive('waitpopup', $k, 'viewevent');
      if ($intoolbar) PopupActive('waitpopup', $k, 'gotoperiod');
      else PopupInvisible('waitpopup', $k, 'gotoperiod');
      $wrv[$k]["waitrg"] = $k;
    }
    popupGen(count($wrv));
    $action->lay->set("POPUPICONS", $action->getParam("WGCAL_U_ICONPOPUP", true));
    
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
