<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: ev_weekview.php,v 1.4 2005/02/02 21:29:38 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage
 */
 /**
 */
include_once("WGCAL/WGCAL_external.php");

function ev_weekview(&$action) {

  $ev = GetHttpVars("ev", -1);
  if ($ev==-1) return;

  $vi = GetHttpVars("vi", "");
  switch ($vi) {
  case "R": $layf = "ev_weekview_resume.xml"; break;
  default: $layf = "ev_weekview_full.xml"; 
  }

  $action->layout = $action->GetLayoutFile($layf);
  $action->lay = new Layout($action->layout, $action);
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $evg = new Doc($dbaccess, $ev);
  switch ($vi) {
  case "R":  $r = ev_weekview_resume($action, $evg); break;
  default: $r = ev_weekview_full($action, $evg); 
  }
  return $r;
}

function ev_weekview_resume(&$action, &$ev) 
{

  $ownerid = $ev->getValue("CALEV_OWNERID");
  $conf    = $ev->getValue("CALEV_VISIBILITY");
  $private = ((($ownerid != $action->user->fid) && ($conf!=0)) ? true : false );
  $action->lay->set("START", substr($ev->getValue("CALEV_START"),0,16));
  $action->lay->set("END", substr($ev->getValue("CALEV_END"),0,16));

  if ($private) $action->lay->set("TITLE", N_("confidential event"));
  else $action->lay->set("TITLE", $ev->getValue("CALEV_EVTITLE"));

  showIcons($action, $ev, $private);

  $tress  = $ev->getTValue("CALEV_ATTID");
  $tresse = $ev->getTValue("CALEV_ATTSTATE");
  $states = CAL_getEventStates($dbaccess,"");
  $valert = "none";
  foreach ($tress as $k => $v) {
    if ($v == $action->user->fid && $tresse[$k]<2) {
      $valert  = "";
      $vtext = $states[$tresse[$k]];
    }
  }
  $action->lay->set("valert", $valert);
  $action->lay->set("vtext", $vtext);
}

function ev_weekview_full(&$action, &$ev) {

  $ownerid = $ev->getValue("CALEV_OWNERID");
  $conf    = $ev->getValue("CALEV_VISIBILITY");
  $private = ($ownerid != $action->user->fid && $conf != 0 ? true : false );

  ev_weekview_resume($action, $ev);

  $action->lay->set("owner", $ev->getValue("CALEV_OWNER"));
  
  $action->lay->set("iconevent", $ev->getIcon($ev->icon));

  $present = false;
  $tress  = $ev->getTValue("CALEV_ATTID");
  $tresse = $ev->getTValue("CALEV_ATTSTATE");
  $bgnew = "lightgrey";
  foreach ($tress as $k => $v) {
    if ($v == $action->user->fid) {
      $present = true;
      switch ($tresse[$k]) {
      case 0: $bgnew = "red"; break;
      case 1: $bgnew = "orange"; break;
      case 2: $bgnew = "lightgreen"; break;
      case 3: $bgnew = "black"; break;
      default: $bgnew = "yellow"; break;
      }
    }
  }
  $action->lay->set("bgstate", $bgnew);

  ev_showattendees($action, $ev, $present);

  $nota = $ev->getValue("CALEV_EVNOTE");
  if ($nota!="" && !$private) {
    $action->lay->set("displaynote", "");
    $action->lay->set("NOTE", $nota);
  } else {
    $action->lay->set("displaynote", "none");
  }    
}


function showIcons(&$action, &$ev, $private) {
  $icons = array();
  if ($private) {
    addIcons($icons, "CONFID");
  } else {
    if ($ev->getValue("CALEV_VISIBILITY") == 1)  addIcons($icons, "VIS_PRIV");
    if ($ev->getValue("CALEV_VISIBILITY") == 2)  addIcons($icons, "VIS_GRP");
    if ($ev->getValue("CALEV_REPEATMODE") != 0)  addIcons($icons, "REPEAT");
    if (count($ev->getTValue("CALEV_ATTID"))>1)  addIcons($icons, "GROUP");
  }
  $action->lay->SetBlockData("icons", $icons);
}

function addIcons(&$ia, $icol)
{

  $ricons = array(
     "CONFID" => array( "iconsrc" => "WGCAL/Images/wm-confidential.png", "icontitle" => "[TEXT:confidential event]" ),
     "VIS_PRIV" => array( "iconsrc" => "WGCAL/Images/wm-private.png", "icontitle" => "[TEXT:visibility private]" ),
     "VIS_GRP" => array( "iconsrc" => "WGCAL/Images/wm-attendees.png", "icontitle" => "[TEXT:visibility group]" ),
     "REPEAT" => array( "iconsrc" => "WGCAL/Images/wm-repeat.png", "icontitle" => "[TEXT:repeat event]" ),
     "GROUP" => array( "iconsrc" => "WGCAL/Images/wm-attendees.png", "icontitle" => "[TEXT:with attendees]" )
  );

  $ia[count($ia)] = $ricons[$icol];
}

function ev_showattendees(&$action, &$ev, $present) {
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $d = new Doc($dbaccess);
  $tress = $ev->getTValue("CALEV_ATTID");
  if ($present && count($tress)>1) {
    $states = CAL_getEventStates($dbaccess,"");
    $action->lay->set("attdisplay","");
    $t = array();
    $tresst = $ev->getTValue("CALEV_ATTTITLE");
    $tresse = $ev->getTValue("CALEV_ATTSTATE");
    $a = 0;
    foreach ($tress as $k => $v) {
      $attru = GetTDoc($action->GetParam("FREEDOM_DB"), $v);
      $t[$a]["atticon"] = $d->GetIcon($attru["icon"]);
      $t[$a]["atttitle"] = $tresst[$k];
      $t[$a]["attstate"] = $states[$tresse[$k]];
      $a++;
    }
    $action->lay->setBlockData("attlist",$t);
  } else {
    $action->lay->set("attdisplay","none");
  }
}
?>
