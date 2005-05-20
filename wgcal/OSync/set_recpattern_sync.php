<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: set_recpattern_sync.php,v 1.3 2005/05/20 16:07:08 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once("WGCAL/Lib.WgcalSync.php");
include_once("FDL/Class.Doc.php");

$evid = GetHttpVars("id", -1);
$type = GetHttpVars("type", "daily");
$days = GetHttpVars("days", "nnnnnnn");
$freq = GetHttpVars("freq", 1);
$end  = GetHttpVars("end", "");

if ($evid==-1) return;

$action = WSyncAuthent();
$db = WSyncGetDataDb();

$event = new Doc($db, $evid);
if (!$event->IsAffected()) exit;


$rtype  = -1;
$rdays  = -1;
$rmonth = -1;

switch($type) {
 case "daily" : $rtype = 1; break;
 case "weekly" : $rtype = 2; break;
 case "monthlyByDay" : $rtype = 3; $month = 1; break;
 case "monthlyByDate" : $rtype = 3; $month = 0; break;
 case "yearly" : $rtype = 4; break;
 default: $rtype = 0; break;
}
 
$event->setValue("calev_repeatmode", $rtype);
if ($rtype == 2) {
  $tda = array();
  for ($id=0; $id<7; $id++) {
    if (substr($days,$id,1)=="y") $tda[] = ($id==0?6:$id-1);
  }
  $event->setValue("calev_repeatweekday", $tda);
}
if ($rtype == 3) $event->setValue("calev_repeatmonth", $month);

$event->setValue("calev_frequency", $freq);

if ($end=="") {
  $event->setValue("calev_repeatuntil", 0);
} else {
  $event->setValue("calev_repeatuntil", 1);
  $event->setValue("calev_repeatuntildate", $end." 23:59:59");
}

$err = $event->Modify();
$err = $event->PostModify();

?>