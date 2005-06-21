<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: event_update_outlookid_sync.php,v 1.5 2005/06/21 09:50:21 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once("Lib.WgcalSync.php");
include_once("Class.WSyncIds.php");

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
