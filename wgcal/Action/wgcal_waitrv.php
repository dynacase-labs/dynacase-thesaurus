<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_waitrv.php,v 1.21 2007/03/09 15:20:03 marc Exp $
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

  $server = getParam("CORE_ABSURL");
  $base  = getParam("CORE_BASEURL");

  $ocount = GetHttpVars("oc", "Y");
  $mode = GetHttpVars("mo", "");
  $action->lay->set("OnlyCount", ($ocount=="Y" && $mode==""?true:false));
  $action->lay->set("LightMode", false);
  $action->lay->set("RSS", false);
  if ($mode=="L") {

    $action->lay->set("LightMode", true);
    $action->lay->set("uptime", strftime("%H:%M %d/%m/%Y", time()));
    header('Content-type: text/xml; charset=utf-8');
    $action->lay->setEncoding("utf-8");

  } else if ($mode=="RSS") {

    $action->lay->set("RSS", true);
    header('Content-type: text/xml; charset=utf-8');
    $action->lay->setEncoding("utf-8");
    $cssf = getparam("CORE_STANDURL")."&app=CORE&action=CORE_CSS&session=".$action->session->id."&layout=FDL:RSS.CSS";
    $action->lay->set("username", $action->user->firstname." ". $action->user->lastname);    
    $action->lay->set("pDate", date("r", time()));
    $action->lay->set("link", setText(($base."app=WGCAL&action=WGCAL_MAIN")));

  } 

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
  $filter[] = "((calev_repeatuntildate > '".$today."' and calev_repeatmode!=0) OR (calev_end>'".$today."' and calev_repeatmode=0)) AND (calev_attid ~* '".$action->user->fid."')";
  $rdoc = GetChildDoc($dbaccess, 0, 0, "ALL", $filter, 
		      $action->user->id, "TABLE", getIdFromName($dbaccess,"CALEVENT"));

  $irv = 0;
  foreach ($rdoc as $k => $v)  {
    $doc = getDocObject($action->GetParam("FREEDOM_DB"), $v);
    $attid = $doc->getTValue("calev_attid");
    $attst = $doc->getTValue("calev_attstate");
    $state = -1;
    foreach ($attid as $ka => $va) {
      $st = intval($attst[$ka]);
      if ($va==$action->user->fid && ($st==EVST_NEW||$st==EVST_READ||$st==EVST_TBC)) $state = $st;
    }
    if ($state!=-1) {
      $date = substr($v["calev_start"],0,11);
      switch ($v["calev_timetype"]) {
      case 1: $date .= " "._("no hour"); break;
      case 2: $date .= " "._("all the day"); break;
      default: $date .= " ".substr($v["calev_start"],11,5);
      }
      $wrv[$irv] = array ( "rvc" => $irv,
			   "id" => $v["id"],
			   "date" =>  $date,
 			   "title" => $v["calev_evtitle"],
 			   "jstitle" => addslashes($v["calev_evtitle"]),
			   "owner" => ucwords(strtolower($v["calev_owner"])),
			   "link" => setText($server.$base."app=FDL&action=IMPCARD&id=".$doc->id),
			   );
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
  } else {
    $action->lay->SetBlockData("WAITRV", null);
    $action->lay->set("RVCOUNT", "0");
  }
  
  setToolsLayout($action, 'waitrv', ($intoolbar?false:true));
  
  
}

function setText($t) {
  return utf8_encode(xmlentities($t));
}

?>
