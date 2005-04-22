<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: event_insert_sync.php,v 1.3 2005/04/22 16:03:29 marc Exp $
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

// global $_GET;
// print_r2($_GET);
// exit;

$ctx = WSyncAuthent();

$debug = (GetHttpVars("debug", 0)==1?true:false);

$dbdata = WSyncGetDataDb($ctx);
$dbadm = WSyncGetAdminDb($ctx);

$event = createDoc($dbdata, "CALEVENT");

$event->id = $ctx->user->id;

$event->setValue("CALEV_OWNERID", $ctx->user->fid);
$u = new Doc($dbdata, $ctx->user->fid);
$event->setValue("CALEV_OWNER", $u->getTitle());

// Ajout en ressource
$event->setValue("CALEV_ATTID", array($ctx->user->fid));
$event->setValue("CALEV_ATTTITLE", array($u->getTitle()));
$event->setValue("CALEV_ATTSTATE", array(EVST_ACCEPT));
$event->setValue("CALEV_ATTGROUP", array(-1));
		 
$event->setValue("CALEV_EVTITLE", utf8_decode(GetHttpVars("name")));
$event->setValue("CALEV_EVNOTE", utf8_decode(GetHttpVars("description")));
$event->setValue("CALEV_VISIBILITY", (GetHttpVars("access","P")=="P"?0:1));

$s_date = GetHttpVars("start_date", "");
$s_time = GetHttpVars("start_time", "");
if ($s_date=="" || $s_time=="") return;
$dur = GetHttpVars("duration", 0);

$event->setValue("CALEV_START", $s_date." ".$s_time);
if ($s_time == "00:00:00" && $dur == 1440) {
  $event->setValue("CALEV_END", $s_date." 23:59:59");
  $event->setValue("CALEV_TIMETYPE", 2);
} else {
  $sfin = WSyncMSdate2Timestamp($s_date, $s_time) + ($dur * 60);
  $event->setValue("CALEV_END", date2db($sfin));
  $event->setValue("CALEV_TIMETYPE", 0);
}

// return event id
$err = $event->Add();
$err = $event->Modify();

WSyncUpdateIds($ctx, $ctx->user->fid, $event->id, GetHttpVars("olookid",0));

$event->AddComment(_("created from outlook, oid=").GetHttpVars("olookid",0));

print $event->id;

?>