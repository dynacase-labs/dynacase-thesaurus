<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_gview.php,v 1.13 2005/08/18 16:44:02 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("WGCAL/Lib.WGCal.php");
include_once('FDL/popup_util.php');
include_once('WHAT/Lib.Common.php');

function wgcal_gview(&$action) {
  global $_POST, $_GET, $ZONE_ARGS;

  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_calendar.js");

  // Set a filter
  $ff = array();
  foreach ($_POST as $k => $v) {
    if (substr($k,0,5)=='rvfs_') $ff[substr($k,5)] = $v;
  }
  foreach ($_GET as $k => $v) {
    if (substr($k,0,5)=='rvfs_') $ff[substr($k,5)] = $v;
  }
  foreach ($ZONE_ARGS as $k => $v) {
    if (substr($k,0,5)=='rvfs_') $ff[substr($k,5)] = $v;
  }
  $filter = array();
  if (count($ff)>0) {
    $i = 0;
    $filter[$i] = "";
    foreach ($ff as $k => $v) {
      switch ($k) {
      case 'int':
	$dd = explode("=", $v);
	break;
      case 'withme' :
	$filter[0] .= (strlen($filter[0])>0 ? " or " : "" ) ."evt_idcreator = ".$action->user->fid;
	$filter[0] .= (strlen($filter[0])>0 ? " or " : "" ) ."evtfc_listattid ~* '".$action->user->fid."'";
	setHttpVar("ress", $action->user->fid);
	break; 
      case 'title': 
	$filter[0] .= (strlen($filter[0])>0 ? " or " : "" ) ."evt_title ~* '".$v."'";
	break;
      case 'desc': 
	$filter[0] .= (strlen($filter[0])>0 ? " or " : "" ) ."evt_desc ~* '".$v."'";
	break;
       case 'ress': 
	setHttpVar("ress", $v);
	break;
     default:
      }
    }
  }

  $themef = GetHttpVars("theme", $action->getParam("WGCAL_U_THEME", "default"));
  @include_once("WGCAL/Themes/default.thm");
  @include_once("WGCAL/Themes/".$themef.".thm");

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $action->lay->set("bcolor", $theme->WTH_COLOR_2);

  $order   = GetHttpVars("order", "D"); // C chocolatine D Decroissant
  $menu    = GetHttpVars("menu", 1); 
  $famids  = GetHttpVars("famids", ""); 
  $explode = ((GetHttpVars("explode", "") == 1)? true : false);
  $twin = GetHttpVars("twin","???");

  // Set producer families
  if ($famids=="") {
    $famids = $action->getParam("WGCAL_G_VFAM", "CALEVENT");
  }
  $ft = explode("|", $famids);
  $fti = array();
  foreach ($ft as $k => $v) $fti[] = getIdFromName($dbaccess, $v);
  $idfamref = implode("|", $fti);
  setHttpVar("idfamref", $idfamref);

  $edre = array();
  $edre = wGetEvents($dd[0], $dd[1]) ; //, $filter);
  if (count($edre) > 0) {
    foreach ($edre as $k => $v) {
      if (!isset($drv[$v["IDP"]])) {
	$drv[$v["IDP"]] = new Doc($dbacess, $v["IDP"]);
// 	echo "(".$v["ID"]."::".$v["IDP"].") id = ".$drv[$v["IDP"]]->id." tsstart = ".$drv[$v["IDP"]]->getValue("calev_start")."<br>";
      }
      if ($drv[$v["IDP"]]->id=="") continue;
      $d = substr($v["TSSTART"],0,2);
      $m = substr($v["TSSTART"],3,2);
      $y = substr($v["TSSTART"],6,4);
      
      $day = strftime("%d %B %Y",mktime(0,0,0,$m,$d,$y));
      $sday = strftime("%Y%m%d",mktime(0,0,0,$m,$d,$y));
      
      if (!isset($btime[$day]["cnt"])) {
	$btime[$day]["sdate"] = $sday;
	$btime[$day]["date"] = $day;
	$btime[$day]["cnt"] = 0;
	$devents[$day] = array();
      }
      $i = $btime[$day]["cnt"];
      $j = count($devents[$day]);
//       if ($action->GetParam("WGCAL_U_PORTALSTYLE", "TABLE")== "TABLE") $devents[$day][$j]["EvCard"] = $drv[$v["IDP"]]->viewdoc($drv[$v["IDP"]]->defaultshorttext);
//       else $devents[$day][$j]["EvCard"] = $drv[$v["IDP"]]->viewdoc($drv[$v["IDP"]]->defaultlongtext);
      $devents[$day][$j]["EvCard"] = $v["EvRCard"];
      $devents[$day][$j]["EditCard"] = ($action->user->fid==$drv[$v["IDP"]]->getValue("calev_ownerid")?true:false);
      $devents[$day][$j]["id"] = $drv[$v["IDP"]]->id;
      $devents[$day][$j]["TSSTART"] = $drv[$v["IDP"]]->getValue("calev_start");
      $devents[$day][$j]["RG"] = $k;
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
  $action->lay->set("title", $title);
  $action->lay->set("standalone", (GetHttpVars("standalone",1)==0?false:true));

}

function daySort($a, $b) {
  if ($a["sdate"] == $b["sdate"]) $r = 0;
  else $r = (($a["sdate"] > $b["sdate"]) ? -1 : 1);
  return $r;
}
function evSort($a, $b) {
  return strcmp($a["start"], $b["start"]);
}
?>
