<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: update_lastsyncdate_sync.php,v 1.2 2005/05/19 16:01:22 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once("WGCAL/Lib.WgcalSync.php");
include_once("WGCAL/Class.WSyncDate.php");

$action = WSyncAuthent();

$win_date = GetHttpVars("win_date", ""); // dd/mm/yyyy
$win_time = GetHttpVars("win_time", ""); // hh:mm:ss

$wtime = WSyncMSdate2Timestamp($win_date,$win_time, true);
if (!$wtime) {
  WSyncError("Invalid date/time format received : [".$win_date."|".$win_time."]");
  return;
}

$db = WSyncGetAdminDb();
$stime = time();
$edate = new WSyncDate($db, $action->parent->user->fid);
$edate->server_date = time();
$edate->outlook_date = $wtime;
if (!$edate->isAffected()) {
  $edate->uid = $action->parent->user->fid;
  $edate->Add();
  $action->log->debug("Add user ".$edate->uid." timestamps(S:".$edate->server_date.",W:".$edate->outlook_date.")");
} else {
  $edate->Modify();
  $action->log->debug("Modify user ".$edate->uid." timestamps(S:".$edate->server_date.",W:".$edate->outlook_date.")");
}  
return;
?>