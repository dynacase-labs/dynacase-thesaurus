<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: event_insert_sync.php,v 1.2 2005/04/19 06:49:51 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once("FDL/Lib.Dir.php");
include_once("FDL/freedom_util.php");
include_once("WGCAL/Lib.WGCal.php");
include_once("WGCAL/Lib.WgcalSync.php");
include_once("WGCAL/Class.WSyncDate.php");
include_once("WGCAL/Class.WSyncIds.php");

$ctx = WSyncAuthent();

$debug = (GetHttpVars("debug", 0)==1?true:false);

$dbdata = WSyncGetDataDb($ctx);
$dbadm = WSyncGetAdminDb($ctx);

$event = createDoc($dbdata, "CALEVENT");

$event->setValue("CALEV_OWNERID", $ctx->user->fid);
$event->setValue("CALEV_OWNER", $ctx->user->title);
$event->setValue("CALEV_EVTITLE", utf8_decode(GetHttpVars("name")));
$event->setValue("CALEV_EVNOTE", utf8_decode(GetHttpVars("description")));
$event->setValue("CALEV_VISIBILITY", (GetHttpVars("access","P")=="P"?0:1)));

$s_date = GetHttpVars("start_date", -1);
$s_time = GetHttpVars("start_time", -1);
$dur = GetHttpVars("duration", -1);

$m_date = GetHttpVars("start_date", -1);
$m_time = GetHttpVars("start_time", -1);




// return event id
$err = $event->Add();
$err = $event->Modify();

WSyncUpdateIds($ctx, $ctx->user->fid, $event->id, GetHttpVars("olookid",0));

print $event->id;

?>