<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Lib.WGCal.php,v 1.16 2005/03/10 18:06:49 marc Exp $
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

  include_once('FDL/popup_util.php');

  // echo "reid=$reid d1=[$d1] d2=[$d2] idres=[$idres]<br>";

  $showrefused = $action->getParam("WGCAL_U_DISPLAYREFUSED", 0);

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $reid=getIdFromName($dbaccess,"WG_AGENDA");
  $tout=array(); 
  $idres = implode("|", $tr);
  setHttpVar("idres",$idres);
  $dre=new Doc($dbaccess,$reid);
  $edre = array();
  $edre=$dre->getEvents($d1,$d2);

  $first = false;

  popupInit('calpopup',  array('editrv',  
			       'viewrv',
                               'deleterv',
                               'acceptrv',
                               'rejectrv',
                               'tbcrv',
                               'historyrv',
                               'cancelrv'));


  foreach ($edre as $k=>$v) {
    $end = ($v["evfc_realenddate"] == "" ? $v["evt_enddate"] : $v["evfc_realenddate"]);
    $item = array( "ID" => $v["id"], 
		   "IDP" => $v["evt_idinitiator"], 
 		   "START" => FrenchDateToUnixTs($v["evt_begdate"]),
  		   "TSSTART" => $v["evt_begdate"],
 		   "TSEND" => $end, 
		   "END" => FrenchDateToUnixTs($end), 
		   "IDC" =>  $v["evt_idcreator"] );

    $ref = false;
    if ($showrefused!=1) {
      $attr = $dre->_val2array($v["evfc_rejectattid"]);
      foreach ($attr as $kat => $vat) {
        if ($action->user->fid == $vat) $ref = true;
      } 
    }
   
    if (!$ref) { 
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

      if ($action->user->fid == $v["evt_idcreator"]) {
	PopupActive('calpopup',$item["RG"], 'editrv');
	PopupActive('calpopup',$item["RG"], 'deleterv');
	$item["action"] = "EDITEVENT";
      }	else {
	PopupInvisible('calpopup',$item["RG"], 'editrv');
	PopupInvisible('calpopup',$item["RG"], 'deleterv');
 	$item["action"] = "VIEWEVENT";
     }

      $withme = false;
      $attr = $dre->_val2array($v["evfc_listattid"]);
      $attrst = $dre->_val2array($v["evfc_listattst"]);
      if (count($attr)>1) {
	foreach ($attr as $ka => $va) {
	  if ($va==$action->user->fid) {
	    $withme = true;
	    $mystate = $attrst[$ka];
	  }
	}
      }

      $conf = $v["evfc_visibility"];
      $private = ((($v["evt_idcreator"] != $action->user->fid) && ($conf!=0)) ? true : false );
      if (!$private) 
	PopupActive('calpopup',$item["RG"], 'historyrv');
      else
	PopupInactive('calpopup',$item["RG"], 'historyrv');
	
      
      if ($withme) {
	PopupActive('calpopup',$item["RG"], 'viewrv');
      } else {
	PopupInvisible('calpopup',$item["RG"], 'viewrv');
      }
      
      if ($withme && $mystate!=2) 
 	PopupActive('calpopup',$item["RG"], 'acceptrv');
      else
	PopupInactive('calpopup',$item["RG"], 'acceptrv');

      if ($withme && $mystate!=3) 
 	PopupActive('calpopup',$item["RG"], 'rejectrv');
      else
	PopupInactive('calpopup',$item["RG"], 'rejectrv');

      if ($withme && $mystate!=4) 
	PopupActive('calpopup',$item["RG"], 'tbcrv');
      else
	PopupInactive('calpopup',$item["RG"], 'tbcrv');
	

      PopupActive('calpopup',$item["RG"], 'cancelrv');

      $tout[] = $item;
  }

  }
  popupGen(count($tout));
  $action->lay->SetBlockData("SEP",array(array("zou")));// to see separator
//   print_r2($tout);
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



function sendRv(&$action, &$event) {
 
 if ($action->getParam("WGCAL_G_SENDMAILS", 0)==0) return;

 $to = $from = $cc = $bcc = "";

 // Compute From:
 $fid = $event->getValue("CALEV_OWNERID");
 $uid = new Doc($action->GetParam("FREEDOM_DB"), $fid);
 $from = $uid->getValue("TITLE")." <".getMailAddr($uid->getValue("US_WHATID")).">";


 // Compute To: field
 $to = "";
 $attid = $event->getTValue("CALEV_ATTID", array()); 
 foreach ($attid as $k => $v) {
   if ($v != $action->user->fid ) {
     $u = new Doc($action->GetParam("FREEDOM_DB"), $v);
     $fullname = $u->getValue("TITLE");
     $mail = getMailAddr($u->getValue("US_WHATID"));
     $to .= ($to==""?"":", ").$fullname." <".$mail.">";
   }
 }

 // Compute Cc: field
 if ($action->GetParam("WGCAL_U_RVMAILCC",0)==1) {
     $u = new Doc($action->GetParam("FREEDOM_DB"), $action->user->fid);
     $cc =  $u->getValue("TITLE")." <".getMailAddr($u->getValue("US_WHATID")).">";
 }
     
 if ($to!="") {
   sendCard($action, $event->id, $to, $cc,
          "["._("event proposal")."] ".$event->title,
          "WGCAL:MAILRV?ev=$event->id:S",
          true, "", $from, $bcc, $format="html" );
  }
}

function GetCalEvent($dbaccess, $ev, $cev) {

  if ($ev<1 && $cev<1) return false;
  if ($ev==-1) {
    $evid = $cev;
  } else {
    $evtmp = new Doc($dbaccess, $ev);
    $evid = $evtmp->getValue("evt_idinitiator");
  }
  if ($evid<1) return false;
  $nev = new Doc($dbaccess, $evid);
  return $nev;
}

?>
