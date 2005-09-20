<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: calev_card.php,v 1.35 2005/09/20 17:14:49 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage
 */
 /**
 */
include_once("FDL/Lib.Color.php");
include_once("WGCAL/Lib.WGCal.php");
include_once("EXTERNALS/WGCAL_external.php");

function calev_card(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $evi = GetHttpVars("id", -1);
  $cev = GetHttpVars("cev", -1);
  $ev = GetCalEvent($dbaccess, $evi, $cev);
  if (!$ev) {
    $action->lay->set("OUT", "No event #$ev");
    return;
  }
  $rg = GetHttpVars("rg", -1);
  $mode  = GetHttpVars("m", "");
  $action->lay->set("mode", ($mode=="v"?"":"none"));

  // matrice de ressource affichée / présentes dans le RV
  $ressd = wgcalGetRessourcesMatrix($ev->id);

  //   $pretitle = "";
  $myid = $action->user->fid;
  $ownerid = $ev->getValue("CALEV_OWNERID");
  $conf    = $ev->getValue("CALEV_VISIBILITY");
  if ($ownerid == $myid) $private = false;
  else if (isset($ressd[$myid])) $private = false;
  else if ($conf==0) $private = false;
  else $private = true;

  $visgroup = false;
  $glist = "";
  if ($conf==2) {
    $visgroup = true;
    $ogrp = $ev->getValue("CALEV_CONFGROUPS");
    $t = explode("|", $ogrp);
    foreach ($t as $k => $v ) {
      if ($v!="") {
	$g  = new_Doc($dbaccess, $v);
	$glist .= ($glist=="" ? "" : ", " ) . ucwords(strtolower($g->title));
      }
    }
  }
  $action->lay->set("ShowGroups", $visgroup);
  $action->lay->set("groups", $glist);
  
      
    
    

  $action->lay->set("ID",    $ev->id);
  
//   $pretitle = $evref.":".$ev->id."::";
  
  $action->lay->set("D_HL","");
  $action->lay->set("D_HR","");
  $action->lay->set("D_LL","");
  $action->lay->set("D_LR","");

  $ldstart = substr($ev->getValue("CALEV_START"),0,10);
  $lstart = substr($ev->getValue("CALEV_START"),11,5);
  $ldend = substr($ev->getValue("CALEV_END"),0,10);
  $lend = substr($ev->getValue("CALEV_END"),11,5);

  switch($ev->getValue("CALEV_TIMETYPE",0)) {

  case 1: 
    $action->lay->set("D_HR",w_strftime(w_dbdate2ts($ldend),WD_FMT_DAYLTEXT));
    $action->lay->set("D_LR",_("no hour")); 
    break;

  case 2: 
    $action->lay->set("D_HR",w_strftime(w_dbdate2ts($ldend),WD_FMT_DAYLTEXT));
    $action->lay->set("D_LR",_("all the day")); 
    break;

  default:
    
    if ($ldend!=$ldstart) {
      $action->lay->set("D_HL",w_strftime(w_dbdate2ts($ldstart),WD_FMT_DAYLTEXT).", ");
      $action->lay->set("D_LL",w_strftime(w_dbdate2ts($ldend),WD_FMT_DAYLTEXT).", ");
      $action->lay->set("D_HR",$lstart);
      $action->lay->set("D_LR",$lend);
    } else {
      $action->lay->set("D_HR",w_strftime(w_dbdate2ts($ldend),WD_FMT_DAYLTEXT));
      $action->lay->set("D_LR",$lstart." - ".$lend);
    }
  }
  $action->lay->set("iconevent", $ev->getIcon($ev->icon));

  $action->lay->set("owner", ucwords(strtolower($ev->getValue("CALEV_OWNER"))));
  $action->lay->set("ShowCategories", false);
  $action->lay->set("ShowDate", false);
  $action->lay->set("modifdate", "");
  $action->lay->set("ShowCalendar", false);
  $action->lay->set("incalendar", "");
  if (!$private) {
    $action->lay->set("ShowDate", true);
    $action->lay->set("modifdate", strftime("%d %B %y %H:%M",$ev->revdate));
  $action->lay->set("ShowCalendar", true);
    $action->lay->set("incalendar", $ev->getValue("CALEV_EVCALENDAR"));
    $show = ($action->getParam("WGCAL_G_SHOWCATEGORIES",0)==1 ? true : false);
    if ($show) {
      $action->lay->set("ShowCategories", $show);
      $catg = wGetCategories();
      $cat = $ev->getValue("CALEV_CATEGORY");
      if (isset($catg[$cat])) $tc = $catg[$cat];
      else $tc = "";
      $action->lay->set("category", $tc);
    }
  }

  if ($private) $title = $pretitle." "._("confidential event");
  else $title = $pretitle." ".$ev->getValue("CALEV_EVTITLE");
  $action->lay->set("TITLE", $title);
  

  $tress  = $ev->getTValue("CALEV_ATTID");
  $tresse = $ev->getTValue("CALEV_ATTSTATE");
  $tressg = $ev->getTValue("CALEV_ATTGROUP");

  // Si je suis convié / j'ai refusé / affichable => Ma couleur
  // Si le propriétaire est dans les affichables / pas refusé => Couleur du propriétaire
  // Si le propriétaire n'est pas affichable => Couleur du premier convié ET AFFICHE et pas refusé.... 
  $showrefused = $action->getParam("WGCAL_U_DISPLAYREFUSED", 0);
  $event_color = "";
  if (isset($ressd[$myid]) 
      && (($ressd[$myid]["state"]==EVST_REJECT && $showrefused==1) || $ressd[$myid]["state"]!=EVST_REJECT )
      && $ressd[$myid]["displayed"]) {
    $event_color = $ressd[$myid]["color"];
  } else {
    if (isset($ressd[$ownerid]) && $ressd[$ownerid]["state"]!=EVST_REJECT &&  $ressd[$ownerid]["displayed"]) {
      $event_color = $ressd[$ownerid]["color"];
    } else {
      while ((list($k,$v) = each($ressd)) && $event_color=="") {
	if ($v["state"]!=EVST_REJECT && $v["displayed"]) $event_color = $v["color"];
      }
    }
  }
    
  $me_attendee = (isset($ressd[$myid]) && $ressd[$myid]["state"]!=EVST_REJECT &&  $ressd[$myid]["displayed"]);

  $bgnew = WGCalGetColorState(EVST_ACCEPT);
  $bgresumecolor = $bgcolor = $event_color;
  if (isset($ressd[$myid]) && $ressd[$myid]["displayed"]) $bgnew = WGCalGetColorState($ressd[$myid]["state"]);

  $action->lay->set("bgstate", $bgnew);
  $action->lay->set("bgcolor", $bgcolor);
  $action->lay->set("bgresumecolor", $bgresumecolor);

  $textcolor = "black";
  $action->lay->set("textcolor", $textcolor);

  // repeat informations
  $action->lay->set("repeatdisplay", "none");
  $tday = array( _("monday"), _("tuesday"),_("wenesday"),_("thursday"),_("friday"),_("saturday"), _("sunday"));
  if (!$private) {
    $rmode = $ev->getValue("CALEV_REPEATMODE", 0);
    $rday = $ev->getTValue("CALEV_REPEATWEEKDAY", -1);
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
        $tr .= ": ";
        foreach ($rday as $kd => $vd) $tr .= $tday[$vd]." ";
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
      

  showIcons($action, $ev, $private, $me_attendee);

  ev_showattendees($action, $ev, $ressd, $private, "lightgrey");

  $nota = str_replace("\n", "<br>", $ev->getValue("CALEV_EVNOTE"));
  if ($nota!="" && !$private) {
    $action->lay->set("displaynote", "");
    $action->lay->set("NOTE", $nota);
  } else {
    $action->lay->set("displaynote", "none");
  }    
}

function showIcons(&$action, &$ev, $private, $withme) {
  $icons = array();
  $sico = $action->GetParam("WGCAL_U_RESUMEICON", 0);
  if ($sico == 1) {
  if ($private) {
    addIcons($icons, "CONFID");
  } else {
    if ($ev->getValue("CALEV_EVCALENDARID") > -1)  addIcons($icons, "CAL_PRIVATE");
    if ($ev->getValue("CALEV_VISIBILITY") == 1)  addIcons($icons, "VIS_PRIV");
    if ($ev->getValue("CALEV_VISIBILITY") == 2)  addIcons($icons, "VIS_GRP");
    if ($ev->getValue("CALEV_REPEATMODE") != 0)  addIcons($icons, "REPEAT");
    if ((count($ev->getTValue("CALEV_ATTID"))>1))  addIcons($icons, "GROUP");
    if ($withme && ($ev->getValue("CALEV_OWNERID") != $action->user->fid)) addIcons($icons, "INVIT");
    if ($ev->getValue("CALEV_EVALARMTIME") > 0) addIcons($icons, "ALARM");
  }
  }
  $action->lay->SetBlockData("icons", $icons);
}

function addIcons(&$ia, $icol)
{
  global $action;

  $ricons = array(
     "CONFID" => array( "iconsrc" => $action->getImageUrl("wm-confidential.gif"), "icontitle" => "[TEXT:confidential event]" ),
     "INVIT" => array( "iconsrc" => $action->getImageUrl("wm-invitation.gif"), "icontitle" => "[TEXT:invitation]" ),
     "VIS_PRIV" => array( "iconsrc" => $action->getImageUrl("wm-private.gif"), "icontitle" => "[TEXT:visibility private]" ),
     "VIS_GRP" => array( "iconsrc" => $action->getImageUrl("wm-privgroup.gif"), "icontitle" => "[TEXT:visibility group]" ),
     "REPEAT" => array( "iconsrc" => $action->getImageUrl("wm-repeat.gif"), "icontitle" => "[TEXT:repeat event]" ),
     "CAL_PRIVATE" => array( "iconsrc" => $action->getImageUrl("wm-privatecalendar.gif"), "icontitle" => "[TEXT:private calendar]" ),
     "ALARM" => array( "iconsrc" => $action->getImageUrl("wm-alarm.gif"), "icontitle" => "[TEXT:alarm]" ),
     "GROUP" => array( "iconsrc" => $action->getImageUrl("wm-attendees.gif"), "icontitle" => "[TEXT:with attendees]" )
  );

  $ia[count($ia)] = $ricons[$icol];
}

function ev_showattendees(&$action, &$ev, $ressd, $private, $dcolor) {

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $globalstate = $dcolor;
  $d = new_Doc($dbaccess);
  $headSet = false;

  $show = true;
  if ($private) $show = false;
  else {
    if (count($ressd)==1) {
      $ownerid = $ev->getValue("CALEV_OWNERID");
      if (isset($ressd[$ownerid])) $show = false;
    }
  }

  if ($show) {
    $states = CAL_getEventStates($dbaccess,"");
    $action->lay->set("attdisplay","inline");

    $attoncol = 5;
    $cola = (count($ressd)> $attoncol ? 2 : 1);
    $curcol = 1;

    $t = array();
    $a = 0;
    foreach ($ressd as $k => $v) {
      if ($v["group"] == -1) {
	if ($k == $action->user->fid) {
	  $headSet = true;
	  $globalstate = WGCalGetColorState($v["state"]);
	}
	$attru = GetTDoc($action->GetParam("FREEDOM_DB"), $k);
	$t[$a]["atticon$curcol"] = $d->GetIcon($attru["icon"]);
	$t[$a]["atttitle$curcol"] = ucwords(strtolower($attru["title"]));
	$t[$a]["attnamestyle$curcol"] = ($v["state"] != EVST_REJECT ? "none" : "line-through");
	$t[$a]["attstate$curcol"] = $states[$v["state"]];
	$t[$a]["TWOCOL"] = ($cola==2 && (count($ressd)>($cola*($a+1)))? true : false );
	if ($curcol == $cola) $a++;
	$curcol = ($curcol==$cola ? 1 : $cola );
      }
    }
    $action->lay->setBlockData("attlist",$t);
  } else {
    $action->lay->set("attdisplay","none");
  }
  $action->lay->set("evglobalstate", $globalstate);
  $action->lay->set("headSet", $headSet);
  $action->lay->set("borderColor", "grey");
}

?>
