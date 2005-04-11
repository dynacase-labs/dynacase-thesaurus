<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: update_lastsyncdate_sync.php,v 1.1 2005/04/11 19:05:42 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once("WGCAL/Lib.WgcalSync.php");
include_once("WGCAL/Class.WSyncDate.php");

$ctx = WSyncAuthent();

$win_date = GetHttpVars("win_date", ""); // dd/mm/yyyy
$win_time = GetHttpVars("win_time", ""); // hh:mm:ss

$wtime = WSyncMSdate2Timestamp($win_date,$win_time);
if (!$wtime) {
  WSyncError($ctx, "Invalid date/time format received : [".$win_date."|".$win_time."]");
  return;
}

$db = WSyncGetAdminDb($ctx);
$stime = time();
$edate = new WSyncDate($db, $ctx->user->id);
$edate->server_date = time();
$edate->outlook_date = $wtime;
if (!$edate->isAffected()) {
  $edate->uid = $ctx->user->id;
  $edate->Add();
  $ctx->log->debug("Add user ".$edate->uid." timestamps(S:".$edate->server_date.",W:".$edate->outlook_date.")");
} else {
  $edate->Modify();
  $ctx->log->debug("Modify user ".$edate->uid." timestamps(S:".$edate->server_date.",W:".$edate->outlook_date.")");
}  
return;
?>