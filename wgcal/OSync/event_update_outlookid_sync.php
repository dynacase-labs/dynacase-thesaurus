<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: event_update_outlookid_sync.php,v 1.2 2005/04/19 06:49:51 marc Exp $
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
WSyncUpdateIds($ctx, $uid, $eid, $oid);
return;
?>