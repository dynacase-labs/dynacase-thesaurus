<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_toolbar.php,v 1.21 2005/03/18 18:58:36 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

include_once("FDL/Class.Doc.php");
include_once('FDL/Lib.Dir.php');
include_once("WGCAL/Lib.WGCal.php");
include_once("WGCAL/WGCAL_external.php");

function wgcal_toolbar(&$action) {

  $action->parent->AddJsRef("jscalendar/Layout/calendar.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-fr.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-setup.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/DHTMLapi.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/PopupWindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/ColorPicker2.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_calendar.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_toolbar.js");


  $action->lay->set("owner", $action->user->lastname." ".$action->user->firstname);
  $action->lay->set("today", strftime("%d/%m/%Y", time()));

//   $cssfile = $action->GetLayoutFile("calendar-default.css");
//   $csslay = new Layout($cssfile,$action);
//   $action->parent->AddCssCode($csslay->gen());

  _waitrv($action);
  _navigator($action);
  _listress($action);

  // Set initial visibility
  $nbTools = 4;
  $all =  explode("|", $action->GetParam("WGCAL_U_TOOLSSTATE", ""));
  $state = array();
  $td = array();
  foreach ($all as $k => $v) {
    $t = explode("%",$v);
    $state[$t[0]] = $t[1];
  }
  for ($i=0; $i<$nbTools; $i++) {
    if (isset($state[$i])) $s = $state[$i];
    else $s = 1;
    $action->lay->set("vTool".$i, ($s==1?"":"none"));
    $td[$i]["iTool"] = $i;
    $td[$i]["sTool"] = $s;
  }
  $action->lay->SetBlockData("InitTools", $td);
  $action->lay->set("countTools", $nbTools);
}


function _seewaitrv(&$action, &$wrv) {

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $rvtextl =  20;

  $irv = count($wrv);
  $rdoc = GetChildDoc($dbaccess, 0, 0, "ALL", array(), 
		      $action->user->fid, "TABLE", getIdFromName($dbaccess,"CALEVENT"));
  foreach ($rdoc as $k => $v)  {
    $doc = new Doc($action->GetParam("FREEDOM_DB"), $v["id"]);
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
      if (strlen($v["calev_evtitle"])>$rvtextl) $wrv[$irv]["wrvtitle"] = addslashes(substr($v["calev_evtitle"],0,$rvtextl)."...");
      else $wrv[$irv]["wrvtitle"] = addslashes($v["calev_evtitle"]);
      $wrv[$irv]["wrvfulldescr"] = "[".$label."] " 
	. substr($v["calev_start"],0,16)." : ".$v["calev_evtitle"]." (".$v["calev_owner"].")";
      $wrv[$irv]["wrvicon"] = $doc->GetIcon($v["icon"]);
      $irv++;
    }
  }
}

function _waitrv(&$action) {

  $trv = array();

  // search NEW rv
  _seewaitrv($action, $trv);


  $action->lay->set("zonealertsize", $action->GetParam("WGCAL_U_ZWRVALERTSIZE", 100));
  $alertfornewevent = $action->GetParam("WGCAL_U_WRVALERT", 1);
  $action->lay->set("alertwrv", "checked");
  if ($alertfornewevent == 0) $action->lay->set("alertwrv", "");

  $rd=getIdFromName($dbaccess,"WG_WAITRV");
  $action->lay->SetBlockData("WAITRV", null);
  $action->lay->set("RVCOUNT", count($trv));
  if (count($trv)>0) {
    $action->lay->SetBlockData("WAITRV", $trv);
    if ($alertfornewevent>0) AddWarningMsg(_("You have waiting events").". (".count($trv).")"); 
  }
  
}

function _navigator(&$action) {

  $ctime = $action->GetParam("WGCAL_U_CALCURDATE", time());
  $cmtime = $ctime * 1000;
  $action->lay->set("CTIME", $ctime);
  $action->lay->set("CmTIME", $cmtime);
  $action->lay->set("Cm2TIME", $cmtime + (30*24*3600 * 1000) );

  $cy = strftime("%Y",$ctime);
  $cys = $cy - 5;
  $cye = $cy + 5;
  $action->lay->set("YSTART", $cys);
  $action->lay->set("YSTOP",$cye );
}



function _listress(&$action)
{

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $i = 0;
  $j = 0;

  $rd = new Doc($dbaccess, $action->user->fid);
  $action->lay->set("myrid", $rd->id);
  $action->lay->set("myricon", $rd->getIcon());
  $action->lay->set("myrdesc", $rd->title);
  $action->lay->set("myrcolor", $action->GetParam("WGCAL_U_MYCOLOR", "white"));

  $curress = $action->GetParam("WGCAL_U_RESSDISPLAYED", "");

  $lress = explode("|", $curress);
  if (count($lress)>0) {
    foreach ($lress as $k => $v) {
      $tt = explode("%", $v);
      $rid = $tt[0];
      $sid = ($tt[1]!="" ? $tt[1] : 0);
      $cid = ($tt[2]!="" ? $tt[2] : "blue");
      $rd = new Doc($dbaccess, $rid);
      if ($rd->IsAffected() && $rd->id != $action->user->fid) {
	$t[$i]["RID"] = $rd->id;
	$t[$i]["RDESCR"] = $rd->title;
	$t[$i]["RICON"] =  $rd->getIcon();
	$t[$i]["RCOLOR"] = $cid;
	$t[$i]["RSTATE"] = $sid;
	if ($sid==1) $t[$i]["RSTYLE"] = "WGCRessSelected";
	else $t[$i]["RSTYLE"] = "WGCRessDefault";
	$i++;
      }
    }
  }
  $action->lay->SetBlockData("L_RESS", $t);

}
?>
