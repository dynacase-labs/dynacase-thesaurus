<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: event_update_outlookid_sync.php,v 1.4 2005/05/19 16:01:22 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once("WGCAL/Lib.WgcalSync.php");
include_once("WGCAL/Class.WSyncIds.php");

$action = WSyncAuthent();
$oid = GetHttpVars("olook_id", -1);
$eid = GetHttpVars("event_id", -1);
if ($oid == -1 || $eid==-1) {
  WSyncError("Invalid outlook id ($oid) or event id ($eid)");
  return;
}
WSyncUpdateIds($eid, $oid);
return;
?>