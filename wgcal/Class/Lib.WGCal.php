<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Lib.WGCal.php,v 1.12 2005/03/07 21:41:49 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

define("SEC_PER_DAY", 24*3600);
define("SEC_PER_HOUR", 3600);
define("SEC_PER_MIN", 60);

define(DATE_F_LONG, "%a %d %B %Y %H:%M:%S");
define(DATE_F_DAY, "%A %d %B %Y");
define(DATE_F_HM, "%H:%M");


// ---------------------------------------------------------------------------------------
// Left bar tools
// ---------------------------------------------------------------------------------------

define(CAL_T_NONE, -1);
define(CAL_T_NAVIGATOR, 0);
define(CAL_T_CALSELECTOR, 1);
define(CAL_T_TODAY, 2);

function WGCalToolSwitchState(&$action, $tool) {
  $val = "";
  $fnd = false;
  $all =  explode("|", $action->GetParam("WGCAL_U_TOOLSSTATE", ""));
  if (count($all)>0) {
    while (list($k, $v) = each($all)) {
      $t = explode("%",$v);
      if ($tool == $t[0]) {
	$ns = ($t[1] == 0 ? 1 : 0 );
	$fnd = true;
      }
      else 
	$ns = $t[1];
      $nv = $t[0]."%".$ns;
      if ($val!="") $val .= "|";
      $val .= $nv;
    }
  } 
  if (!$fnd) {
    if ($val!="") $val .= "|";
    $val = $tool."%1";
  }
  $action->parent->param->set("WGCAL_U_TOOLSSTATE", $val, PARAM_USER.$action->user->id, $action->parent->id);
}

function WGCalToolIsVisible( &$action,  $tool ) {
  $state = false;
  $all =  explode("|", $action->GetParam("WGCAL_U_TOOLSSTATE", ""));
  if (count($all)>0) {
    while (list($k, $v) = each($all)) {
      $t = explode("%",$v);
      if ($t[0] == $tool) {
	$state = $t[1];
      }
    }
  }
  if ($state==1) $state = true;
  return $state;
}

function WGCalGetRessDisplayed(&$action) {
  $r = array();
  $ir = 0;
  $cals = explode("|", $action->GetParam("WGCAL_U_RESSTMPLIST", $action->GetParam("WGCAL_U_RESSDISPLAYED", $action->user->id)));
  while (list($k,$v) = each($cals)) {
    if ($v!="") {
      $tc = explode("%", $v);
      if ($tc[0] != "" && $tc[1] == 1) {
	$r[$ir]->id = $tc[0];
	if ($tc[0] == $action->user->fid) $r[$ir]->color = $action->GetParam("WGCAL_U_MYCOLOR", "black");
	else $r[$ir]->color = $tc[2]; 
	$ir++;
      }
    }
  }
  return $r;
}

function WGCalGetDayFromTs($ts) 
{
  if ($ts<=0) return false;
  $dd = strftime("%d", $ts);
  $mm = strftime("%m", $ts);
  $yy = strftime("%Y", $ts);
  $fwdt = mktime ( 0, 0, 0, $mm, $dd, $yy);
  return $fwdt;
}

function WGCalGetFirstDayOfWeek($ts) {
	if ($ts<=0) return false;
	$iday  = strftime("%u",$ts);
	$dt = 1-$iday;
        $tsfwd = $ts - (($iday-1) * SEC_PER_DAY);
	$dd = strftime("%d", $tsfwd);
 	$mm = strftime("%m", $tsfwd);
 	$yy = strftime("%Y", $tsfwd);
	$fwdt = mktime ( 0, 0, 0, $mm, $dd, $yy);
	return $fwdt;
}
function WGCalGetFirstDayOfMonth($ts) {
	if ($ts<=0) return false;
 	$mm = strftime("%m", $ts);
 	$yy = strftime("%Y", $ts);
	$fwdt = mktime ( 0, 0, 0, $mm, 1, $yy);
	return $fwdt;
}
 function WGCalGetFirstDayOfMonthN($ts) {
 	return strftime("%u", $ts);
}
      	
function WGCalGetAgendaEvents(&$action,$tr,$d1="",$d2="") 
{
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $reid=getIdFromName($dbaccess,"WG_AGENDA");
  $tout=array(); 
  $idres = implode("|", $tr);
  setHttpVar("idres",$idres);
  //echo "reid=$reid d1=[$d1] d2=[$d2] idres=[$idres]<br>";
  $dre=new Doc($dbaccess,$reid);
  $edre=$dre->getEvents($d1,$d2);
  $first = false;
  foreach ($edre as $k=>$v) {
    $end = ($v["evfc_realenddate"] == "" ? $v["evt_enddate"] : $v["evfc_realenddate"]);
    $item = array( "REF" => $v["id"], 
		   "ID" => $v["evt_idinitiator"],
 		   "TSSTART" => $v["evt_begdate"],
 		   "START" => FrenchDateToUnixTs($v["evt_begdate"]),
		   "TSEND" => $end, 
		   "END" => FrenchDateToUnixTs($end), 
// 		   "TSEND" => $v["evt_enddate"], 
// 		   "END" => FrenchDateToUnixTs($v["evt_enddate"]), 
		   "IDC" =>  $v["evt_idcreator"] );
    $n = new Doc($dbaccess, $v["evt_idinitiator"]);
    $varclass = get_class_vars("_CALEVENT");
    if (isset($varclass["ZoneEvtAbstract"]) && isset($varclass["ZoneEvtCard"])) {
      $abstract =  $varclass["ZoneEvtAbstract"];
      $view = $varclass["ZoneEvtCard"];
    } else {
      $abstract =  "WGCAL:DEFAULTABS";
      $view = "WGCAL:DEFAULTVIEW";
    }
    $item["RESUME"] = $abstract;
    $item["VIEW"] = $view;
    $item["RG"] = count($tout);
    $tout[] = $item;
  }
    print_r2($tout);
  return $tout;
}
       	
function WGCalDaysInMonth($ts) 
{
  $timepieces = getdate($ts);
  $thisYear          = $timepieces["year"];
  $thisMonth        = $timepieces["mon"];
  for($thisDay=1;checkdate($thisMonth,$thisDay,$thisYear);$thisDay++);
  return ($thisDay-1);
} 

function dbdate2ts($dbtime) {
  $sec = substr($dbtime,17,2);
  $min = substr($dbtime,14,2);
  $hou = substr($dbtime,11,2);
  $day = substr($dbtime,0,2);
  $mon = substr($dbtime,3,2);
  $yea = substr($dbtime,6,4);
  return mktime($hou, $min, $sec, $mon, $day, $yea);
}

function sendEventMail(&$action, $evid) {
  return;
}



function WGCalGetRGroups(&$action, $uid) {

  $dbaccess = $action->GetParam("COREUSER_DB");
  $u = new User($dbaccess, $uid);
  $ug = $u->GetGroupsId();
  foreach ($ug as $k=>$v) {
    $gu = new User($dbaccess, $v);
    $fug[] = $gu->fid;
  }
  return $fug;
      
}

// Pour identifiant WHAT
// Class.User.php
//   /**
//    * get All ascendant group ids of the user object
//    */
//   function GetGroupsId()

// Par FREEDOM :
//  $u->getTValue("US_IDGROUP");


// Cela ne donne que les groupes directs. Il faut ensuite utiliser une autre fonctions pour avoir les pères de chacun des groupes d'appartenance.
// Class.Group.php /**
//    * get all parent (ascendant) group of this group
//    * @return array id
//    */
//   function getParentsGroupId($pgid="", $level=0) {

?>
