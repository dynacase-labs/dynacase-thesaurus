<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_cronalert.php,v 1.1 2006/11/15 18:08:32 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once('WHAT/Lib.Common.php');
include_once('FDL/Class.Doc.php');
include_once('WGCAL/WGCAL_external.php');
include_once('WGCAL/Lib.WGCal.php');

global $action;
$action->log = new Log("", "WGCAL", "REMINDER");

$dbaccess = $action->GetParam("FREEDOM_DB");
$reqid = getIdFromName($dbaccess,"WG_ALLEVENTS");
if (!is_numeric($reqid) || $reqid<=0) {
  $action->log->error("Can't open document WG_ALLEVENTS, check installation");
  exit;
}
$dreq = new_Doc($dbaccess, $reqid);

$iuser = GetHttpVars("user", 0);

$filter = array();
if ($iuser>0) {
  $filter[0] = "(evfc_listattid ~ '\\\y(".$iuser.")\\\y' )";
  $action->log->info("processing event for user freedom id ".$iuser);
 }
 
$intervals = CalListAlarmInterval();
$min = 0;
foreach ($intervals as $ki => $vi)   $min = ($min>$ki || $min==0? $ki : $min );

// $intervals = array( "120" => _("Two hours") );

$lct = time();
$today = date("Y-m-d H:i",$lct);
$action->log->info("start at [$today], running every ".$min." minutes");

$ctime = mktime(strftime("%H",$lct), strftime("%M",$lct), 0, strftime("%m",$lct), strftime("%d",$lct), strftime("%Y",$lct));

foreach ($intervals as $ki => $vi) {

  if (!is_numeric($ki) ||  $ki==0) continue;

  $filter[1] = "evfc_alarm = $ki";

  $dstart = $ctime + ($ki*60);
  $tds = date("Y-m-d H:i:s",$dstart);
  $jtds = Iso8601ToJD($tds);
  $dend   = $dstart + ($min*60) - 1;
  $tde = date("Y-m-d H:i:s",$dend);
  $jtde = Iso8601ToJD($tde);

  $action->log->info(sprintf("%20s : [ ".$tds." (".$jtds.") - ".$tde." (".$jtde.") ] ",$vi));

  $events = $dreq->getEvents($tds, $tde, true, $filter);

  $evbyowner = array();
  foreach ($events as $ke => $ve) {
//     showlog("\t ".$ve["id"]." ".$ve["title"]." ".$ve["evt_begdate"]);
    $jstart = StringDateToJD($ve["evt_begdate"]);
    if ($jstart>=$jtds && $jstart<=$jtde) {
      $attid = Doc::_val2array($ve["evfc_listattid"]);
      $attst = Doc::_val2array($ve["evfc_listattst"]);
      foreach ($attid as $ku => $vu) {
	if ($attst[$ku]!=2) continue;
	// Only for accepted RV
	if (!isset($evbyowner[$vu])) {
	  $evbyowner[$vu] = array();
	}
	$evbyowner[$vu][] = array( "k"=> $ke, "idf"=> $ve["evt_idinitiator"]);
      }
    }
  }
//   print_r2($evbyowner);

  if (count($evbyowner)>0) {
    $title = $action->getParam("WGCAL_G_MARKFORMAIL", "[RDV]")." "._("event alarm mail title")." : ";
    foreach ($evbyowner as $ku => $vu) {

      $du = getTDoc($dbaccess, $ku);
      $umail = getMailAddr($du["us_whatid"], true);
      $remind = $action->parent->param->getUParam("WGCAL_U_REMINDMODE", $du["us_whatid"],  $action->parent->GetIdFromName("WGCAL"));
      if ($umail!="") {
	if ($remind==2) $action->log->info("User ".$du["title"]." doesn't want receive reminder");
	else {
	  foreach ($vu as $krv => $vrv) {
	    if ($remind==0 || ($remind==1 && ($events[$vrv["k"]]["owner"]=$du["us_whatid"]))) {
	      $action->log->info("Send reminder to user ".$du["title"]." (remind=$remind mail=".$umail.") for event [".$vrv["idf"].":".$events[$vrv["k"]]["title"]."]");
	      sendCard($action, $vrv["idf"], $umail, "", $title.$events[$vrv["k"]]["title"], 
		       "WGCAL:MAILRV?ev=".$vrv["idf"].":S&msg="._("event alarm mail content"),
		       true,
		       "",
		       $umail,
		       "",
		       "html",
		       false,
		       array() );
	    }
	  }
	}
      } else {
	$action->log->warning("User ".$du["title"]." (id=$ku) have no mail adress");
      }
    }
  }

}

?>  
