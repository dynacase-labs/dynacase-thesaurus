<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Lib.WGCal.php,v 1.43 2005/06/27 17:01:56 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once("FDL/mailcard.php");
include_once("osync/Class.WSyncDate.php");
include_once("WGCAL/Lib.wTools.php");
include_once("EXTERNALS/WGCAL_external.php");

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

function wgcalGetRessourcesMatrix($id=-1) {
  global $action;

  $event = new Doc($action->getParam("FREEDOM_DB"), $id);
  $tress  = $event->getTValue("CALEV_ATTID");
  $tresse = $event->getTValue("CALEV_ATTSTATE");
  $tressg = $event->getTValue("CALEV_ATTGROUP");

  $ressd = array();
  foreach ($tress as $k => $v) {
    if (!(isset($ressd[$v]) && $tressg[$k]==-1)) {
      $ressd[$v]["state"] = $tresse[$k];
      $ressd[$v]["color"] = "white";
      $ressd[$v]["displayed"] = false;
      $ressd[$v]["group"] = $tressg[$k];
    }
  }

  $cals = explode("|", $action->GetParam("WGCAL_U_RESSDISPLAYED", $action->user->id));
  while (list($k,$v) = each($cals)) {
    if ($v!="") {
      $tc = explode("%", $v);
      if ($tc[0] != "" && isset($ressd[$tc[0]])) {
	$ressd[$tc[0]]["displayed"] = ($tc[1] == 1 ? true : false );
	$ressd[$tc[0]]["color"] = $tc[2];
      }
    }
  }
  return $ressd;
}


function WGCalGetDayFromTs($ts) 
{
  if ($ts<=0) return false;
  $dd = strftime("%d", $ts);
  $mm = strftime("%m", $ts);
  $yy = strftime("%Y", $ts);
  $fwdt = gmmktime ( 0, 0, 0, $mm, $dd, $yy);
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
	$fwdt = gmmktime ( 0, 0, 0, $mm, $dd, $yy);
	return $fwdt;
}
function WGCalGetFirstDayOfMonth($ts) {
	if ($ts<=0) return false;
 	$mm = strftime("%m", $ts);
 	$yy = strftime("%Y", $ts);
	$fwdt = gmmktime ( 0, 0, 0, $mm, 1, $yy);
	return $fwdt;
}
 function WGCalGetFirstDayOfMonthN($ts) {
 	return strftime("%u", $ts);
}
      	
function WGCalGetAgendaEvents(&$action,$displress,$d1="",$d2="", $nofilter=false) {

  include_once('FDL/popup_util.php');

  $debug = false;
//       $debug = true;


  $dbaccess = $action->GetParam("FREEDOM_DB");
  $reid=getIdFromName($dbaccess,"WG_AGENDA");
  $tout=array(); 
  $idres = implode("|", $displress);
  setHttpVar("idres",$idres);

  $fref = $action->getParam("WGCAL_G_VFAM", "CALEVENT");
  $ft = explode("|", $fref);
  $fti = array();
  foreach ($ft as $k => $v) {
    $fti[] = (is_numeric($v) ? $v : getIdFromName($dbaccess, $v));
  }
  $idfamref = implode("|", $fti);

//      echo "reid=$reid d1=[$d1] d2=[$d2] idres=[$idres] idfamref=[$idfamref]<br>";

  setHttpVar("idfamref", $idfamref);
  $dre=new Doc($dbaccess,$reid);
  $edre = array();
  $edre=$dre->getEvents($d1,$d2);

  $first = false;

  popupInit('calpopup',  array('editrv', 'deloccur', 'viewrv', 'deleterv',
                               'acceptrv', 'rejectrv', 'tbcrv', 'historyrv',
                               'cancelrv'));

  
  $showrefused = $action->getParam("WGCAL_U_DISPLAYREFUSED", 0);
  $rvfamid = getIdFromName($dbaccess, "CALEVENT");
  
  foreach ($edre as $k=>$v) {
    $end = ($v["evfc_realenddate"] == "" ? $v["evt_enddate"] : $v["evfc_realenddate"]);
    $item = array( "ID" => $v["id"], 
		   "IDP" => $v["evt_idinitiator"], 
 		   "START" => localFrenchDateToUnixTs($v["evt_begdate"]),
  		   "TSSTART" => $v["evt_begdate"],
 		   "TSEND" => $end, 
		   "END" => localFrenchDateToUnixTs($end), 
		   "IDC" =>  $v["evt_idcreator"] );
    $displayEvent = true;

    // Traitement de refus => spécifique à CALEVENT
    if ($v["evt_frominitiatorid"] == $rvfamid && !$nofilter) {

      $displayEvent = false;
      
      // Affichage
      // - si une ressource affiché est dedans et pas refusé
      // - si une ressource affiché est dedans et pas refusé
      $attlist  = $dre->_val2array($v["evfc_listattid"]);
      $attrstat = $dre->_val2array($v["evfc_listattst"]);
      $attinfo = array();
      foreach ($attlist as $kat => $vat) {
	$attinfo[$vat]["status"] = $attrstat[$kat];
	$attinfo[$vat]["display"] = isset($displress[$vat]);
      }
      
      foreach ($attinfo as $kat => $vat) {
	
//  	echo "(me:".$action->user->fid.") [".$v["id"]."] Ressource #$kat affichée:".($vat["display"]?"oui":"non");
	if ($vat["display"]) {

	  if ($action->user->fid!=$kat) {
	    if ($vat["status"]!=EVST_REJECT) {
//  	      echo " Ressource X, status:".$vat["status"]."!=EVST_REJECT";
	      $displayEvent = true;
	    }
	  } else {
//  	    echo " (vat(status)=".$vat["status"]." showrefused=$showrefused)";
	    if ($vat["status"]!=EVST_REJECT || $showrefused==1) {
//  	      echo " Moi, showrefused=$showrefused status:".$vat["status"]."!=EVST_REJECT";
	      $displayEvent = true;
	    }
	  }
	}
//  	echo "<br>";
      }
    }

    if ($displayEvent) { 

      $n = new Doc($dbaccess, $v["id"]);  
      $item["RESUME"] = $n->calVResume;
      $item["VIEW"] = $n->calVCard;
      $item["VIEWLTEXT"] = $n->calVLongText;
      $item["VIEWSTEXT"] = $n->calVShortText;
      $item["RG"] = count($tout);
      
      PopupInvisible('calpopup',$item["RG"], 'acceptrv');
      PopupInvisible('calpopup',$item["RG"], 'rejectrv');
      PopupInvisible('calpopup',$item["RG"], 'tbcrv');
      PopupInactive('calpopup',$item["RG"], 'historyrv');
      PopupActive('calpopup',$item["RG"], 'viewrv');
      PopupInvisible('calpopup',$item["RG"], 'deloccur');
      PopupActive('calpopup',$item["RG"], 'cancelrv');
      PopupInactive('calpopup',$item["RG"], 'editrv');
      PopupInactive('calpopup',$item["RG"], 'deleterv');
      $action->lay->set("popupState",false);
      
      if ($action->user->fid == $v["evt_idcreator"]) {
	if ($v["evfc_repeatmode"] > 0) PopupActive('calpopup',$item["RG"], 'deloccur');
	PopupActive('calpopup',$item["RG"], 'editrv');
	PopupActive('calpopup',$item["RG"], 'deleterv');
	$item["action"] = "EDITEVENT";
      }	else {
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
      if (!$private) PopupActive('calpopup',$item["RG"], 'historyrv');
      else PopupInactive('calpopup',$item["RG"], 'viewrv');
      
      if ($withme) {
        $action->lay->set("popupState",true);
        if ($mystate!=2) PopupActive('calpopup',$item["RG"], 'acceptrv');
        if ($mystate!=3) PopupActive('calpopup',$item["RG"], 'rejectrv');
        if ($mystate!=4) PopupActive('calpopup',$item["RG"], 'tbcrv');
      }
      
      $tout[] = $item;
    }
  }
  popupGen(count($tout));
  $action->lay->SetBlockData("SEP",array(array("zou")));// to see separator
  if ($debug) print_r2($tout);
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
  return gmmktime($hou, $min, $sec, $mon, $day, $yea);
}
function localFrenchDateToUnixTs($fdate) {
  if (preg_match("/^(\d\d)\/(\d\d)\/(\d\d\d\d)\s?(\d\d)?:?(\d\d)?:?(\d\d)?\s?(\w+)?$/", $fdate,$r)) {   
    $ds = gmmktime($r[4], $r[5], $r[6], $r[2], $r[1], $r[3]);
  } else {
    $ds = -1;
  }
  return $ds;
}



function WGCalGetRGroups(&$action, $uid) {
  $fug = array();
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


/*
 * $sendto = 0: mail is send to the rv owner
 *         = 1: mail is send to all attendees
 *         = 2: mail is send to all attendees except to the owner
 */
function sendRv(&$action, &$event, $sendto=0, $reason="") {
 
 if ($action->getParam("WGCAL_G_SENDMAILS", 0)==0) return;

 $to = $from = $cc = $bcc = "";

 // Compute From:
 $fid = $event->getValue("CALEV_OWNERID");
 $uid = new Doc($action->GetParam("FREEDOM_DB"), $fid);
 $from = $uid->getValue("TITLE")." <".getMailAddr($uid->getValue("US_WHATID")).">";
 if ($action->GetParam("WGCAL_U_RVMAILCC",0)==1) $bcc = $from;


 // Compute To: field
 $to = "";
 if ($sendto==1 || $sendto==2) {
   $attid = $event->getTValue("CALEV_ATTID", array()); 
   foreach ($attid as $k => $v) {
     if ($v != $action->user->fid ) {
       $u = new Doc($action->GetParam("FREEDOM_DB"), $v);
       $fullname = $u->getValue("TITLE");
       $mail = getMailAddr($u->getValue("US_WHATID"));
       if ($mail!="") $to .= ($to==""?"":", ").$fullname." <".$mail.">";
     }
   }
 }
 if ($sendto==0 || $sendto==1) {
   $to .= ($to==""?"":", ").$uid->getValue("TITLE")." <".getMailAddr($uid->getValue("US_WHATID")).">";
 }

     
  if ($to!="") {
    //echo "to= [$to] cc=[$cc] subject=[".$action->getParam("WGCAL_G_MARKFORMAIL", "[RDV]")." ".$event->title."] zone=["."WGCAL:MAILRV?ev=$event->id:S&msg=$reason"."] from=[".$from."] bcc=[".$bcc."]<br>";
    sendCard($action, 
	     $event->id, 
	     $to, 
	     $cc,
	     $action->getParam("WGCAL_G_MARKFORMAIL", "[RDV]")." ".$event->title,
	     "WGCAL:MAILRV?ev=$event->id:S&msg=$reason",
	     true, 
	     "", 
	     $from, 
	     $bcc, 
	     "html", 
	     false );
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


function GroupExplode(&$action, $gid) {

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $groupfid = getIdFromName($dbaccess, "GROUP");
  $igroupfid = getIdFromName($dbaccess, "IGROUP");
  $g = new Doc($dbaccess, $gid);
  if ($g->fromid!=$groupfid && $g->fromid!=$igroupfid) return array($gid);

  $udbaccess = $action->GetParam("COREUSER_DB");
  $ugrp = new User($udbaccess);
  $ulist = $ugrp->GetUsersGroupList($g->getValue("US_WHATID"));
  foreach ($ulist as $ku=>$vu)	$gr[] = $vu["fid"];
  return $gr;
}
 
function WGCalEvSetColor(&$action, &$event) {

  $dress = wGetRessDisplayed();

  // the current user is event owner ?
  $idcolor = ($action->user->fid == $event["evt_idcreator"] ? $event["evt_idcreator"] : -1);
  // the current user is an event ressource ?
  if ($idcolor == -1) {
    $tress = explode("|",$event["evt_idres"]);
    foreach ($tress as $kr => $vr) {
      if ($vr==$action->user->fid) $idcolor = $vr;
    }
  }
  // search for the first user in ressource
  if ($idcolor == -1) {
    while ((list($kdr, $vdr) = each($dress)) && $dcolor==-1) {
      while ((list($kr, $vr) = each($tress)) && $dcolor==-1) {
	if ($vr==$vdr->id) $idcolor = $vr;
      }
    }
  }

  if ($idcolor!=-1)  {
    foreach ($dress as $kr => $vr) {
      if ($idcolor == $vdr->id) return $vdr->color;
    }
  }
  return "lightgrey";  
}

function GetLastSyncDate($db) {
  global $action;
  $syncdate = new WSyncDate($db, $action->parent->user->fid);
  return ($syncdate->outlook_date);
}

?>
