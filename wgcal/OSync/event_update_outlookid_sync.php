<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: event_update_outlookid_sync.php,v 1.1 2005/04/18 15:39:30 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once("WGCAL/Lib.WgcalSync.php");
include_once("WGCAL/Class.WSyncIds.php");

$ctx = WSyncAuthent();
$oid = GetHttpVars("olook_id", -1);
$eid = GetHttpVars("event_id", -1);
if ($oid == -1 || $eid==-1) {
  WSyncError("Invalid outlook id ($oid) or event id ($eid)");
  return;
}
$user_id = $ctx->user->id;
$db = WSyncGetAdminDb($ctx);
$evids = new WSyncIds($db, array($uid, $eid));
if ($evids->IsAffected()) {
  $evids->outlook_id = $oid;
  $evids->Modify();
  $ctx->log->debug("Update oid for event($uid,$eid)");
} else {
  $evids->user_id = $uid;
  $evids->event_id = $eid;
  $evids->outlook_id = $oid;
  $evids->Add();
  $ctx->log->debug("Add ids for event($uid,$eid,$oid)");
}
return;
?>