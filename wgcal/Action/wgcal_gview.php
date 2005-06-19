<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_gview.php,v 1.9 2005/06/19 17:37:33 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_gview(&$action) {
  global $order;

  $themef = GetHttpVars("theme", $action->getParam("WGCAL_U_THEME", "default"));
  @include_once("WGCAL/Themes/default.thm");
  @include_once("WGCAL/Themes/".$themef.".thm");


  $dbaccess = $action->GetParam("FREEDOM_DB");

  $action->lay->set("bcolor", $theme->WTH_COLOR_2);

  $ds      = GetHttpVars("ds","");
  $de      = GetHttpVars("de","");
  $order   = GetHttpVars("order", "D"); // C chocolatine D Decroissant
  $title   = GetHttpVars("title" , ""); 
  $filteron  = GetHttpVars("filteron" , ""); 
  $menu    = GetHttpVars("menu", 1); 
  $famids  = GetHttpVars("famids", ""); 
  $ressids = GetHttpVars("rlist", $action->user->fid); 
  $explode = ((GetHttpVars("explode", "") == 1)? true : false);
  $standalone = (GetHttpVars("standalone", "Y")=="Y" ? true : false);
  
  $action->lay->set("styleFIELDSET", false);
  $action->lay->set("styleTABLE", false);
  if (GetHttpVars("mode","") == "FIELDSET") $action->lay->set("styleFIELDSET", true);
  else $action->lay->set("styleTABLE", true);

//     echo "search ds=$ds de=$de order=$order explode=".($explode?"true":"false")." title=$title filteron=$filteron famids=$famids ressids=$ressids<br>";

  // Set producer families
  if ($famids=="") {
    $famids = $action->getParam("WGCAL_G_VFAM", "CALEVENT");
  }
  $ft = explode("|", $famids);
  $fti = array();
  foreach ($ft as $k => $v) $fti[] = getIdFromName($dbaccess, $v);
  $idfamref = implode("|", $fti);
  setHttpVar("idfamref", $idfamref);

  // Set event ressources
  setHttpVar("idres", $ressids);

  // Set a filter
  $evfilter = array();
  $ff = "";
  if ($title!="") {
    if ($filteron == "" || $filteron == "title") $ff = "(evt_title ~* '".$title."')";
    if ($filteron == "" || $filteron == "desc") {
      $ff .= ($ff == "" ? "" : " or ");
      $ff .= "(evt_desc ~* '".$title."')";
    }
    $evfilter[] = $ff;
  }
  $reid=getIdFromName($dbaccess,"WG_AGENDA");
  $dre = new Doc($dbaccess,$reid);
  $edre = array();
  $edre = $dre->getEvents($ds,$de, true, $evfilter);


  $calevent = getIdFromName($dbaccess,"CALEVENT");

  if (count($edre)>0) {

    foreach ($edre as $k => $v) {
      
      $refused = false;
      if ($v["evt_frominitiatorid"] == $calevent ) {
	$attr = Doc::_val2array($v["evfc_rejectattid"]);
	foreach ($attr as $kat => $vat) {
	  if ($action->user->fid == $vat) $refused = true;
	}
      }
      

      if (!$refused) {

	$doctmp = new Doc($dbaccess, $v["evt_idinitiator"]);

	$day = substr($v["evt_begdate"],0,10);
	$tday = strftime("%A %d %b",dbdate2ts($v["evt_begdate"]));
      
	if (!isset($btime[$day]["cnt"])) {
	  $btime[$day]["date"] = $day;
	  $btime[$day]["tdate"] = $tday;
	  $btime[$day]["cnt"] = 0;
	  $devents[$day] = array();
	}
	$i = $btime[$day]["cnt"];
      
      
	$j = count($devents[$day]);
	$hs = substr($v["evt_begdate"],11,5);
	$he = substr(($v["evfc_realenddate"]==""?$v["evt_enddate"]:$v["evfc_realenddate"]),11,5);
	if ($hs == $he) {
	  $devents[$day][$j]["start"] = _("no hour");
	  $devents[$day][$j]["end"] = "";
	  $devents[$day][$j]["isHour"] = false;
	} else if ($hs == "00:00" && $he == "23:59") {
	  $devents[$day][$j]["start"] = _("all the day");
	  $devents[$day][$j]["end"] = "";
	  $devents[$day][$j]["isHour"] = false;
	} else {
	  $devents[$day][$j]["isHour"] = true;
	  $devents[$day][$j]["start"] = $hs;
	  $devents[$day][$j]["end"] = $he;
	}
	$devents[$day][$j]["id"] = $v["id"];
	$devents[$day][$j]["fid"] = $v["evfc_idinitiator"];
	$devents[$day][$j]["date"] = $day;
	$devents[$day][$j]["title"] = $v["title"];
	$devents[$day][$j]["desc"] = $v["evt_desc"];
	if ($v["evt_desc"]=!"") $devents[$day][$j]["HaveDesc"] = true;
	else $devents[$day][$j]["HaveDesc"] = false;
	  $devents[$day][$j]["owner"] = $v["evt_creator"];
	if ($v["evt_idcreator"] == $action->user->fid) $devents[$day][$j]["showowner"] = false;
	else $devents[$day][$j]["showowner"] = true;
	$devents[$day][$j]["icon"] = $doctmp->GetIcon($v["icon"]);     
	$btime[$day]["cnt"]++;
      }
    }
    if (count($btime)>0) {
      uasort($btime, "daySort");
      $action->lay->setBlockData("btime", $btime);
      if (count($devents)>0) {
	foreach ($devents as $k => $v) { 
	  uasort($devents[$k], "evSort");
	  $action->lay->setBlockData("devents$k", $devents[$k]);
	}
	$action->lay->set("noresult", false);
      } else {
	$action->lay->set("noresult", true);
      }
    } else {
	$action->lay->set("noresult", true);
    }
  } else {
    $action->lay->set("noresult", true);
    $action->lay->setBlockData("btime", null);
    $btime = array();
  }
  $action->lay->set("standalone", $standalone);
  $action->lay->set("title", $title);
  
}

function daySort($a, $b) {
  global $order;
  if ($order!="C") {
    $t = $a;
    $a = $b;
    $b = $t;
  }
  $ad = substr($a["date"],6,4) . substr($a["date"],3,2) . substr($a["date"],0,2);
  $bd = substr($b["date"],6,4) . substr($b["date"],3,2) . substr($b["date"],0,2);
  if ($ad == $bd) $r = 0;
  else $r = (($ad > $bd) ? -1 : 1);
  return $r;
}
function evSort($a, $b) {
  return strcmp($a["start"], $b["start"]);
}
?>
