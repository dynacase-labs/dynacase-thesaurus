<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: calev_viewltext.php,v 1.3 2005/06/05 09:02:09 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage
 */
 /**
 */
include_once("WGCAL/Lib.WGCal.php");
include_once("EXTERNALS/WGCAL_external.php");

function calev_viewltext(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $evi = GetHttpVars("id", -1);
  $cev = GetHttpVars("cev", -1);
  $ev = GetCalEvent($dbaccess, $evi, $cev);

  $private = IsPrivate($ev, $action->user->fid);

  $action->lay->set("id",      $ev->id);
  if ($private) $action->lay->set("title", $pretitle." "._("confidential event"));
  else $action->lay->set("title", $pretitle." ".$ev->getValue("CALEV_EVTITLE"));
  $action->lay->set("owner", $ev->getValue("CALEV_OWNER"));

  switch($ev->getValue("CALEV_TIMETYPE",0)) {
  case 1: 
    $date = substr($ev->getValue("CALEV_END"),0,10)." ".$lrhs = _("no hour");
    break;
  case 2: 
    $date = substr($ev->getValue("CALEV_END"),0,10)." "._("all the day");;
    break;
  default:
    $ldstart = substr($ev->getValue("CALEV_START"),0,10);
    $lstart = substr($ev->getValue("CALEV_START"),11,5);
    $ldend = substr($ev->getValue("CALEV_END"),0,10);
    $lend = substr($ev->getValue("CALEV_END"),11,5);
    if ($ldend==$ldstart) {
      $date = $ldend.", ".$lstart." - ".$lend;
    } else {
      $date = $ldstart." ".$lstart." - ".$ldend." ".$lend;
    }
  }
  $action->lay->set("dates", $date);

  $present = IsPresent($ev, $action->user->fid);

  $ticons = showIcons2($action, $ev, $private, $present);
  $action->lay->SetBlockData("icons", $ticons);

}
function IsPrivate(&$ev, $rid) {
  $ownerid = $ev->getValue("CALEV_OWNERID");
  $conf    = $ev->getValue("CALEV_VISIBILITY");
  return  ((($ownerid != $rid) && ($conf!=0)) ? true : false );
}

function IsPresent(&$ev, $rid) {
  $tress = $ev->getTValue("CALEV_ATTID");
  $present = false;
  foreach ($tress as $k => $v) {
    if ($v == $rid) {
      $present = true;
    }
  }
  return $present;
}

function showIcons2(&$action, &$ev, $private, $present) {
  $icons = array();
  if ($private) {
    addIcons2($icons, "CONFID");
  } else {
    if ($ev->getValue("CALEV_VISIBILITY") == 1)  addIcons2($icons, "VIS_PRIV");
    if ($ev->getValue("CALEV_VISIBILITY") == 2)  addIcons2($icons, "VIS_GRP");
    if ($ev->getValue("CALEV_REPEATMODE") != 0)  addIcons2($icons, "REPEAT");
    if ((count($ev->getTValue("CALEV_ATTID"))>1 && $present) ||  (count($ev->getTValue("CALEV_ATTID"))>0 && !$present))  addIcons2($icons, "GROUP");
    if ($present && ($ev->getValue("CALEV_OWNERID") != $action->user->fid)) addIcons2($icons, "INVIT");
  }
  return $icons;
}

function addIcons2(&$ia, $icol)
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

