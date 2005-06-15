<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_toolbar.php,v 1.37 2005/06/15 17:36:59 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

include_once("FDL/Class.Doc.php");
include_once('FDL/Lib.Dir.php');
include_once("WGCAL/Lib.WGCal.php");
include_once("osync/Lib.WgcalSync.php");
include_once("EXTERNALS/WGCAL_external.php");

function wgcal_toolbar(&$action) {

  if ($action->getParam("WGCAL_U_TBCONTACTS",0)) $action->lay->set("SHOWCONTACTS", true);
  else $action->lay->set("SHOWCONTACTS", false);

  if ($action->getParam("WGCAL_U_TBSEARCH",0)) $action->lay->set("SHOWSEARCH", true);
  else $action->lay->set("SHOWSEARCH", false);

  if ($action->getParam("WGCAL_U_TBTODOS",1)) $action->lay->set("SHOWTODOS", true);
  else $action->lay->set("SHOWTODOS", false);

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
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_waitzone.js");


  $action->lay->set("MyFreedomId", $action->user->fid);
  $action->lay->set("owner", $action->user->lastname." ".$action->user->firstname);
  $action->lay->set("today", strftime("%d/%m/%Y", time()));
  $db = WSyncGetAdminDb();
  $lsync = GetLastSyncDate($db);
  if ($lsync=="") $action->lay->set("LSYNC", false);
  else {
    $action->lay->set("LSYNC", true);
    $action->lay->set("lastsync", substr(WSyncTs2Outlook($lsync),0,16));
    $action->lay->set("lsyncstyle", ((time()-$lsync)>(24*3600*7)?"color:red":""));
  }
    
//   $cssfile = $action->GetLayoutFile("calendar-default.css");
//   $csslay = new Layout($cssfile,$action);
//   $action->parent->AddCssCode($csslay->gen());

  _waitrv();
  _navigator($action);
  _listress($action);

  // Set initial visibility
  $all =  explode("|", $action->GetParam("WGCAL_U_TOOLSSTATE", ""));
  $state = array();
  foreach ($all as $k => $v) {
    $t = explode("%",$v);
    $state[$t[0]] = ($t[1]==0?"none":"");
  }
  $toolList = array( 'inav', 'vvical', 'waitrv', 'todo');
  foreach ($toolList as $k => $v ) {
    if (isset($state[$v])) $vis = $state[$v];
    else $vis = 1;
    $action->lay->set("v".$v, $vis);
  }

  $todoviewday = $action->getParam("WGCAL_U_TODODAYS", 7);
  $action->lay->set("tododays", $todoviewday);
}


function _seewaitrv(&$wrv) {
  global $action;

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $rvtextl =  20;
  $today = date2db(time()-(24*3600*7), false)." 00:00:00";
  $filter[] = "(calev_start > '".$today."' ) AND (calev_attid ~* '".$action->user->fid."')";
  $irv = count($wrv);
  $rdoc = GetChildDoc($dbaccess, 0, 0, "ALL", $filter, 
		      $action->user->id, "TABLE", getIdFromName($dbaccess,"CALEVENT"));

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
      $wrv[$irv]["tsdate"] = dbdate2ts($v["calev_start"]);
      $irv++;
    }
  }
}

function _waitrv() {
  global $action;
  $trv = array();

  // search NEW rv
  _seewaitrv($trv);

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $action->lay->set("zonealertsize", $action->GetParam("WGCAL_U_ZWRVALERTSIZE", 100));
  $alertfornewevent = $action->GetParam("WGCAL_U_WRVALERT", 1);
  $action->lay->set("alertwrv", "checked");
  if ($alertfornewevent == 0) $action->lay->set("alertwrv", "");

  // Init popup
  include_once("FDL/popup_util.php");
  popupInit('waitpopup',  array('acceptevent',  'refuseevent', 'viewevent', 'gotoperiod', 'cancelevent'));
  foreach ($trv as $k => $v) {
    PopupActive('waitpopup', $k, 'acceptevent');
    PopupActive('waitpopup', $k, 'refuseevent');
    PopupActive('waitpopup', $k, 'viewevent');
    PopupActive('waitpopup', $k, 'gotoperiod');
    PopupActive('waitpopup', $k, 'cancelevent');
    $trv[$k]["waitrg"] = $k;
  }
  popupGen(count($trv));
  $action->lay->set("POPUPICONS", $action->getParam("WGCAL_U_ICONPOPUP", true));
    


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


  $curress = $action->GetParam("WGCAL_U_RESSDISPLAYED", "");

  $lress = explode("|", $curress);

  $cuser = false;
  foreach ($lress as $k => $v) {
    $tt = explode("%", $v);
    if ($tt[0] == $action->user->fid) $cuser = true;
  }

  if (!$cuser) $lress[count($lress)] = $action->user->fid."%1%yellow";

  // Init popup
  include_once("FDL/popup_util.php");
  popupInit('resspopup',  array('displayress',  'changeresscolor', 'removeress', 'onlyme', 'invertress', 'displayallr', 'hideallr', 'cancelress'));
  foreach ($lress as $k => $v) {
    $tt = explode("%", $v);
    $rid = $tt[0];
    $sid = ($tt[1]!="" ? $tt[1] : 0);
    $cid = ($tt[2]!="" ? $tt[2] : "blue");
    $rd = new Doc($dbaccess, $rid);
    if ($rd->IsAffected()) {
      if ($rd->id == $action->user->fid) PopupInactive('resspopup', $rd->id, 'removeress');
      else PopupActive('resspopup', $rd->id, 'removeress');
      $t[$i]["RG"] = $i;
      $t[$i]["RID"] = $rd->id;
      $t[$i]["RDESCR"] = addslashes($rd->getTitle());
      $t[$i]["RICON"] =  $rd->getIcon();
      $t[$i]["RCOLOR"] = $cid;
      $t[$i]["RSTATE"] = $sid;
      if ($sid==1) $t[$i]["RSTYLE"] = "WGCRessSelected";
      else $t[$i]["RSTYLE"] = "WGCRessDefault";
      PopupActive('resspopup', $rd->id, 'displayress');
      PopupActive('resspopup', $rd->id, 'changeresscolor');
      PopupActive('resspopup', $rd->id, 'hideallr');
      PopupActive('resspopup', $rd->id, 'displayallr');
      PopupActive('resspopup', $rd->id, 'onlyme');
      PopupActive('resspopup', $rd->id, 'invertress');
      PopupActive('resspopup', $rd->id, 'cancelress');
      
      $i++;
    }
  }

  popupGen(count($t));
  $action->lay->SetBlockData("L_RESS", $t);

}
?>
