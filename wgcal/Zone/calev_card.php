<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: calev_card.php,v 1.18 2005/06/07 16:05:36 marc Exp $
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

//   $pretitle = "";

  $ownerid = $ev->getValue("CALEV_OWNERID");
  $conf    = $ev->getValue("CALEV_VISIBILITY");
  $private = ((($ownerid != $action->user->fid) && ($conf!=0)) ? true : false );
  $tpriv = array();

  $action->lay->set("ID",    $ev->id);
  $tpriv[0]["ID"] = $ev->id;
  
//   $pretitle = $evref.":".$ev->id."::";

  $ldstart = $ldend = $lstart = $lend = $lrhs = $lrhe = ""; 
  switch($ev->getValue("CALEV_TIMETYPE",0)) {
  case 1: 
    $ldstart = $lrhs = _("no hour"); 
    $ldend = substr($ev->getValue("CALEV_END"),0,10);
    break;
  case 2: 
    $ldstart = $lrhs = _("all the day"); 
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
  $cardheight = "100%";
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

  if ($private) $action->lay->set("TITLE", $pretitle." "._("confidential event"));
  else $action->lay->set("TITLE", $pretitle." ".$ev->getValue("CALEV_EVTITLE"));

  $tress  = $ev->getTValue("CALEV_ATTID");
  $tresse = $ev->getTValue("CALEV_ATTSTATE");
  $tressg = $ev->getTValue("CALEV_ATTGROUP");


// Si je suis convié et affichable => Ma couleur
// Si le propriétaire est dans les affichables => Couleur du propriétaire
// Si le propriétaire n'est pas affichable => Couleur du premier convié qui est affichable. 
  $me_attendee = false;
  $cstate = -1;
  foreach ($tress as $k => $v) {
    if ($v == $action->user->fid) {
      $me_attendee = true;
      $cstate = $tresse[$k];
    }
  }
  $display_me = false;
  $ress = WGCalGetRessDisplayed($action);
  foreach ($ress as $k => $v ) if ($v->id == $action->user->fid) $display_me = true;

  $ress_color = -1;
  if ($display_me && $me_attendee) $ress_color = $action->user->fid;
  else  {
    foreach ($ress as $k => $v ) if ($v->id ==  $ownerid) $ress_color = $ownerid;
    if ( $ress_color == -1) {
       foreach ($tress as $k => $v ) {
         foreach ($ress as $kv => $vv) if ($v == $vv->id) $ress_color = $vv->id;
       }
    }
  }
  $bgresumecolor = $bgcolor = "white";
  foreach ($ress as $k => $v) if ($v->id==$ress_color) $bgresumecolor=$bgcolor=$v->color;
//echo "[".$ev->getValue("CALEV_EVTITLE")."] display_me=$display_me me_attendee=$me_attendee ress_color=$ress_color<br>";

  if ($display_me) $bgnew = WGCalGetColorState($cstate);
  else $bgnew = "transparent";
  $action->lay->set("bgstate", $bgnew);
  $action->lay->set("bgcolor", $bgcolor);
  $action->lay->set("bgresumecolor", $bgresumecolor);

  $textcolor = "black";
  $action->lay->set("textcolor", $textcolor);

  if ($private && !$display_me) $action->lay->SetBlockData("ISCONF", null);
  else $action->lay->SetBlockData("ISCONF", $tpriv);

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

  ev_showattendees($action, $ev, $display_me, "lightgrey");

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
     "GROUP" => array( "iconsrc" => $action->getImageUrl("wm-attendees.gif"), "icontitle" => "[TEXT:with attendees]" )
  );

  $ia[count($ia)] = $ricons[$icol];
}

function ev_showattendees(&$action, &$ev, $display_me, $dcolor) {
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $globalstate = $dcolor;
  $headSet = false;
  $d = new Doc($dbaccess);
  $tress = $ev->getTValue("CALEV_ATTID");
  if (count($tress)>1) {
    $states = CAL_getEventStates($dbaccess,"");
    $action->lay->set("attdisplay","");
    $t = array();
    $tresst = $ev->getTValue("CALEV_ATTTITLE");
    $tresse = $ev->getTValue("CALEV_ATTSTATE");
    $tressg = $ev->getTValue("CALEV_ATTGROUP");
    $a = 0;
    foreach ($tress as $k => $v) {
      if ($tressg[$k] == -1) {
	if ($v == $action->user->fid && $tresse[$k] == EVST_REJECT)  {
	  $globalstate = "black";
	  $headSet = true;
	} else if ($tresse[$k] != EVST_ACCEPT && $tresse[$k] != EVST_REJECT) {
	  if ($v == $action->user->fid) $globalstate = "red";
	  else if ($globalstate != "red") $globalstate = "orange";
	  $headSet = true;
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
    $action->lay->set("headSet", $headSet);
    $action->lay->set("borderColor", "grey");
}

?>
