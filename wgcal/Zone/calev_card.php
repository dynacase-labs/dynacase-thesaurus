<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: calev_card.php,v 1.3 2005/03/03 20:10:22 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage
 */
 /**
 */
include_once("WGCAL/Lib.WGCal.php");
include_once("WGCAL/WGCAL_external.php");
include_once("FDL/popup_util.php");

function calev_card(&$action) {

//   global $_POST, $_GET;
//   echo "avant"; print_r2($_POST); print_r2($_GET); echo "apres";

  $evid = GetHttpVars("ev", -1);
  $evref = GetHttpVars("ref", -1);
  $mode  = GetHttpVars("mode", "h");
  if ($evid==-1) return;

  $action->lay->set("mode", ($mode=="h"?"none":""));

  popupInit('calevcard'.$evid,  array('editrv', 
				'deleterv', 
				'acceptrv', 
				'rejectrv', 
				'tbcrv', 
				'historyrv', 
				'cancelrv'));

  PopupInactive('calevcard'.$evid,$evid,'editrv');
  PopupInactive('calevcard'.$evid,$evid,'deleterv');
  PopupInactive('calevcard'.$evid,$evid,'acceptrv');
  PopupInactive('calevcard'.$evid,$evid,'rejectrv');
  PopupInactive('calevcard'.$evid,$evid,'tbcrv');
  PopupInactive('calevcard'.$evid,$evid,'historyrv');
  PopupActive('calevcard'.$evid,$evid,'cancelrv');
  $action->lay->SetBlockData("SEP",array(array("zou")));// to see separator

//   $pretitle = "";

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $ev = new Doc($dbaccess, $evid);

  $ownerid = $ev->getValue("CALEV_OWNERID");
  $conf    = $ev->getValue("CALEV_VISIBILITY");
  $private = ((($ownerid != $action->user->fid) && ($conf!=0)) ? true : false );
  $tpriv = array();

  $action->lay->set("REF",   $evref);
  $tpriv[0]["REF"] = $evref;
  $action->lay->set("ID",    $ev->id);
  $tpriv[0]["ID"] = $ev->id;
//    $pretitle = $evref.":".$ev->id."::";


  $ldstart = $ldend = $lstart = $lend = $lrhs = $lrhe = ""; 
  $cardheight = "100%";
  switch($ev->getValue("CALEV_TIMETYPE",0)) {
  case 1: 
    $ldstart = $lrhs = _("no hour"); 
    $ldend = substr($ev->getValue("CALEV_END"),0,10);
    break;
  case 2: 
    $ldstart = $lrhs = _("all the day"); 
    $cardheight = "100%";
    $ldend = substr($ev->getValue("CALEV_END"),0,10);
    break;
  default:
    
    $ldstart = substr($ev->getValue("CALEV_START"),0,10);
    $lstart = substr($ev->getValue("CALEV_START"),11,5);
    $ldend = substr($ev->getValue("CALEV_END"),0,10);
    $lend = substr($ev->getValue("CALEV_END"),11,5);
    if ($ldend!=$ldstart) {
      $lrhs = substr($ev->getValue("CALEV_START"),0,5)." ";
      $lrhe = substr($ev->getValue("CALEV_END"),0,5)." ";
    }
    $lrhs .= substr($ev->getValue("CALEV_START"),11,5);
    $lrhe .= substr($ev->getValue("CALEV_END"),11,5);
  }
  $action->lay->set("cardheight", $cardheight);
  $action->lay->set("DSTART", $ldstart);
  $action->lay->set("START", $lstart);
  if ($ldstart == $ldend ) $ldend = "";
  $action->lay->set("DEND",   $ldend);
  $action->lay->set("END",   $lend);
  $action->lay->set("RHOURS", $lrhs);
  $action->lay->set("RHOURE", $lrhe);

  $action->lay->set("iconevent", $ev->getIcon($ev->icon));

  $action->lay->set("owner", $ev->getValue("CALEV_OWNER"));
  $action->lay->set("modifdate", strftime("%x %X",$ev->revdate));
  $action->lay->set("incalendar", $ev->getValue("CALEV_EVCALENDAR"));

  $ress = WGCalGetRessDisplayed($action);
  if ($private) $action->lay->set("TITLE", $pretitle." "._("confidential event"));
  else $action->lay->set("TITLE", $pretitle." ".$ev->getValue("CALEV_EVTITLE"));

  $tress  = $ev->getTValue("CALEV_ATTID");
  $tresse = $ev->getTValue("CALEV_ATTSTATE");
  $tressg = $ev->getTValue("CALEV_ATTGROUP");


  // Compute color according the owner, participant, etc,...
  $o_or_p = 0;
  $v_proprio = false;
  foreach ($ress as $k => $v) {
    if ($action->user->fid == $v) $v_proprio = true;
  }
  
  if ($v_proprio) {
    if ($action->user->fid == $ownerid) $o_or_p = $ownerid;
    else {
       foreach ($tress as $k => $v) if ($action->user->fid == $v) $o_or_p = $action->user->fid;
    }
  } else {
    foreach ($ress as $k => $v) if ($v->id == $ownerid) $o_or_p = $ownerid;
    if ($o_or_p==0) {
      foreach ($ress as $k => $v) {
        foreach ($tress as $kr => $vr) {
          if ($v->id==$vr && $tressg[$kr]==-1) $o_or_p = $vr;
        }
      }
    }
  }
  $bgresumecolor = $bgcolor = "white";
  foreach ($ress as $k => $v) if ($v->id==$o_or_p) $bgresumecolor=$bgcolor=$v->color;

  $present = false;
  $cstate = -1;
  foreach ($tress as $k => $v) {
    if ($v == $action->user->fid) {
      $present = true;
      $cstate = $tresse[$k];
    }
  }
  if ($present) $bgnew = WGCalGetColorState($cstate);
  else $bgnew = "transparent";
  $action->lay->set("bgstate", $bgnew);
  $action->lay->set("bgcolor", $bgcolor);
  $action->lay->set("bgresumecolor", $bgresumecolor);

  if ($present) {
    Popupactive('calevcard'.$evid,1,'acceptrv');
    Popupactive('calevcard'.$evid,1,'rejectrv');
    Popupactive('calevcard'.$evid,1,'tbcrv');
  }

  if ($private && !$present) $action->lay->SetBlockData("ISCONF", null);
  else $action->lay->SetBlockData("ISCONF", $tpriv);

  // repeat informations
  $action->lay->set("repeatdisplay", "none");
  $tday = array( _("monday"), _("tuesday"),_("wenesday"),_("thursday"),_("friday"),_("saturday"), _("sunday"));
  if (!$private) {
    $rmode = $ev->getValue("CALEV_REPEATMODE", 0);
    $rday = $ev->getValue("CALEV_REPEATWEEKDAY", -1);
    $rmonth = $ev->getValue("CALEV_REPEATMONTH", -1);
    $runtil = $ev->getValue("CALEV_REPEATUNTIL", 0);
    $runtild = $ev->getValue("CALEV_REPEATUNTILDATE", "");
    $rexclude = $ev->getValue("CALEV_EXCLUDEDATE", array());
    if ($rmode>0 && $rmode<5) {
      $action->lay->set("repeatdisplay", "");
      switch ($rmode) {
      case 1:
        $tr = _("dayly");
        break;
      case 2:
        $tr = _("weekly");
        $tr .= " (".$tday[$rday].")";
        break;
      case 3:
        $tr = _("monthly");
        if ($rmonth==0) $tr .= " ("._("by date").")";
        if ($rmonth==1) $tr .= " ("._("by day").")";
        break;
      case 4: $tr = _("yearly"); break;
      }
      $tru = "";
      if ($runtil==1 && $runtild>0) $tru = " "._("until")." ".substr($runtild,0,10);
      if (count($rexclude)>0) $tru .= " "._("there is excluded days");
      $action->lay->set("repeatinfos", $tr);
      $action->lay->set("repeatuntil", $tru);
    }

  }
      

  showIcons($action, $ev, $private, $present);

  ev_showattendees($action, $ev, $present, "lightgrey");

  $nota = $ev->getValue("CALEV_EVNOTE");
  if ($nota!="" && !$private) {
    $action->lay->set("displaynote", "");
    $action->lay->set("NOTE", $nota);
  } else {
    $action->lay->set("displaynote", "none");
  }    
   popupGen($evid);
}

function showIcons(&$action, &$ev, $private, $present) {
  $icons = array();
  if ($private) {
    addIcons($icons, "CONFID");
  } else {
    if ($ev->getValue("CALEV_VISIBILITY") == 1)  addIcons($icons, "VIS_PRIV");
    if ($ev->getValue("CALEV_VISIBILITY") == 2)  addIcons($icons, "VIS_GRP");
    if ($ev->getValue("CALEV_REPEATMODE") != 0)  addIcons($icons, "REPEAT");
    if ((count($ev->getTValue("CALEV_ATTID"))>1 && $present) ||  (count($ev->getTValue("CALEV_ATTID"))>0 && !$present))  addIcons($icons, "GROUP");
    if ($present && ($ev->getValue("CALEV_OWNERID") != $action->user->fid)) addIcons($icons, "INVIT");
  }
  $action->lay->SetBlockData("icons", $icons);
}

function addIcons(&$ia, $icol)
{

  $ricons = array(
     "CONFID" => array( "iconsrc" => "WGCAL/Images/wm-confidential.png", "icontitle" => "[TEXT:confidential event]" ),
     "INVIT" => array( "iconsrc" => "WGCAL/Images/wm-invitation.png", "icontitle" => "[TEXT:invitation]" ),
     "VIS_PRIV" => array( "iconsrc" => "WGCAL/Images/wm-private.png", "icontitle" => "[TEXT:visibility private]" ),
     "VIS_GRP" => array( "iconsrc" => "WGCAL/Images/wm-privgroup.png", "icontitle" => "[TEXT:visibility group]" ),
     "REPEAT" => array( "iconsrc" => "WGCAL/Images/wm-repeat.png", "icontitle" => "[TEXT:repeat event]" ),
     "GROUP" => array( "iconsrc" => "WGCAL/Images/wm-attendees.png", "icontitle" => "[TEXT:with attendees]" )
  );

  $ia[count($ia)] = $ricons[$icol];
}

function ev_showattendees(&$action, &$ev, $present, $dcolor) {
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $globalstate = $dcolor;
  $globalstatesize = "0";
  $d = new Doc($dbaccess);
  $tress = $ev->getTValue("CALEV_ATTID");
  if ((count($tress)>1 && $present) ||  (count($tress)>0 && !$present)) {
    $states = CAL_getEventStates($dbaccess,"");
    $action->lay->set("attdisplay","");
    $t = array();
    $tresst = $ev->getTValue("CALEV_ATTTITLE");
    $tresse = $ev->getTValue("CALEV_ATTSTATE");
    $tressg = $ev->getTValue("CALEV_ATTGROUP");
    $a = 0;
    foreach ($tress as $k => $v) {
      if ($tressg[$k] == -1) {
	if ($tresse[$k] != EVST_ACCEPT && $tresse[$k] != EVST_REJECT) {
	  if ($v == $action->user->fid) $globalstate = "red";
	  else if ($globalstate != "red") $globalstate = "orange";
	  $globalstatesize = "3";
	}
	$attru = GetTDoc($action->GetParam("FREEDOM_DB"), $v);
	$t[$a]["atticon"] = $d->GetIcon($attru["icon"]);
	$t[$a]["atttitle"] = $tresst[$k];
	$t[$a]["attnamestyle"] = ($tresse[$k] != EVST_REJECT ? "none" : "line-through");
	$t[$a]["attstate"] = $states[$tresse[$k]];
	$a++;
      }
    }
    $action->lay->setBlockData("attlist",$t);
  } else {
    $action->lay->set("attdisplay","none");
  }
    $action->lay->set("evglobalstate", $globalstate);
    $action->lay->set("headsize", $globalstatesize);
}


?>
