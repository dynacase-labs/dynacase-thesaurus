<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_gview.php,v 1.22 2006/04/22 05:14:45 marc Exp $
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
  if (count($_POST)>0) foreach ($_POST as $k => $v) {
    if (substr($k,0,5)=='rvfs_') $ff[substr($k,5)] = $v;
  }
  if (count($_GET)>0) foreach ($_GET as $k => $v) {
    if (substr($k,0,5)=='rvfs_') $ff[substr($k,5)] = $v;
  }
  if (count($ZONE_ARGS)>0) foreach ($ZONE_ARGS as $k => $v) {
    if (substr($k,0,5)=='rvfs_') $ff[substr($k,5)] = $v;
  }
  $filter = array();
  $explode = true;
  $dd[0] = strftime("%Y-%m-%d 00:00:00", time()-(3600*24*365));
  $dd[1] = strftime("%Y-%m-%d 23:59:59", time()+(3600*24*365));
  if (count($ff)>0) {
    foreach ($ff as $k => $v) {
      switch ($k) {
      case 'ts':
	$dd[0] = gmdate("Y-m-d H:i:00",$v);
	break;
      case 'te':
	$dd[1] = gmdate("Y-m-d H:i:00",$v);
	break;
      case 'int':
	$dd = explode("=", $v);
	break;
      case 'withme' :
	setHttpVar("ress", $action->user->fid);
	break; 
      case 'search': 
	$sphrase = $v;
	$filter[] = "(evt_title ~* '".$v."') or (evt_desc ~* '".$v."')";
	break;
       case 'pexc': 
	$filter[] = "(evt_idinitiator != ".$v.")";
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

  $menu    = GetHttpVars("menu", 1); 
  $famids  = GetHttpVars("famids", ""); 

  $action->lay->set("standalone", (GetHttpVars("stda")==1?false:true));
  $action->lay->set("sphrase", $sphrase);

  // Set producer families
  if ($famids=="") $famids = $action->getParam("WGCAL_G_VFAM", "CALEVENT");
  $ft = explode("|", $famids);
  $fti = array();
  foreach ($ft as $k => $v) $fti[] = getIdFromName($dbaccess, $v);
  $idfamref = implode("|", $fti);
  setHttpVar("idfamref", $idfamref);
  $evt = array();
  
  $evt = array();
//    echo "start=".$dd[0]." end=".$dd[1]."<br>";
//    print_r($filter);
  $evt = wGetEvents($dd[0], $dd[1], $explode, $filter); 
  if (count($evt) > 0) {
    $td = array();
    $edoc = array();
    $hsep = "&nbsp;-&nbsp;";
    foreach ($evt as $ke=> $ve) {
      if (!isset($edoc[$ve["id"]])) $edoc[$ve["id"]] = getDocObject($dbaccess, $ve);
      $cday = substr($ve["evt_begdate"],6,4).substr($ve["evt_begdate"],3,2).substr($ve["evt_begdate"],0,2);
      $j = count($td[$cday]);
      $td[$cday]["date"] = $cday;
      $td[$cday]["datestr"] = strftime("%d %B %Y",mktime(0,0,0,substr($ve["evt_begdate"],3,2),substr($ve["evt_begdate"],0,2),substr($ve["evt_begdate"],6,4)));
      $td[$cday]["ev"][$j]["id"] = $ve["id"];
      $td[$cday]["ev"][$j]["idp"] = $edoc[$ve["id"]]->getValue("evt_idinitiator");
      $dstart = substr($ve["evt_begdate"], 0, 2);
      $end = ($ve["evfc_realenddate"] == "" ? $ve["evt_enddate"] : $ve["evfc_realenddate"]);
      $dend = substr($end, 0, 2);
      $hstart = substr($ve["evt_begdate"],11,5);
      $hend = substr($end,11,5);
      if ($hstart==$hend && $hend=="00:00") {
	$hours = "("._("no hour").")";
 	$p = 0;
      } else if ($hstart=="00:00" && $hend=="23:59") {
	$hours = "("._("all the day _ short").")";
 	$p = 1;
      } else {
	if ($dend==$dstart) {
	  $hours = $hstart."$hsep".$hend;
	  $p = 4;
	} else { 
	  if ($id==$dstart) {
	    $hours = $hstart."$hsep..."; //" 24:00";
	    $p = 4;
	  } else if ($id==$dend) {
	    $hours = "...$hsep".$hend; // "00:00 ".$lend;
	    $p = 2;
	  } else {
	    $hours = "("._("all the day _ short").")";
	    $p = 1;
	  }
	}
      }
      $td[$cday]["ev"][$j]["hour"] = $hours;
      $td[$cday]["ev"][$j]["p"] = $p;
      $td[$cday]["ev"][$j]["Icons"] = "";
      $it = $edoc[$ve["id"]]->getIconsBlock();
      if (count($it)) {
	foreach ($it as $ki => $vi) $td[$cday]["ev"][$j]["Icons"] .= "<img src=\"".$vi["icosrc"]."\">";
	$td[$cday]["ev"][$j]["Icons"] .= "&nbsp;";
      }
      $td[$cday]["ev"][$j]["title"] = $edoc[$ve["id"]]->getTitleInfo();
      $td[$cday]["ev"][$j]["owner"] = $edoc[$ve["id"]]->getValue("evt_creator");
      $td[$cday]["ev"][$j]["edit"] = $edoc[$ve["id"]]->isEditable();
      $td[$cday]["ev"][$j]["note"] = $ve["evt_desc"];
      $td[$cday]["ev"][$j]["vNote"] = ($ve["evt_desc"]==""?false:true);
      if ($sphrase!="") {
	$td[$cday]["ev"][$j]["note"] = preg_replace('/('.$sphrase.'?)/', '<span style="background:yellow">\1</span>', $td[$cday]["ev"][$j]["note"]);
 	$td[$cday]["ev"][$j]["title"] = preg_replace('/('.$sphrase.'?)/', '<span style="background:yellow">\1</span>', $td[$cday]["ev"][$j]["title"]);
      }
			 
    }
    uasort($td, "daySort");
    $action->lay->setBlockData("btime", $td);
    foreach ($td as $k => $v) {
      uasort($td[$k], "evSort");
      $action->lay->setBlockData("devents$k", $td[$k]["ev"]);
    }
    $action->lay->set("noresult", false);
  } else {
    $action->lay->set("noresult", true);
  }    
}

function daySort($a, $b) {
  return $a["date"] - $b["date"];
}
function evSort($a, $b) {
  if ($e1["pound"]==$e2["pound"] && $e2["pound"]>3) return strcmp($e1["hour1"], $e2["hour1"]);
  return $e1["pound"] > $e2["pound"];
}
?>
