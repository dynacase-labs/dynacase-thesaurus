<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: event_update_sync.php,v 1.1 2005/05/20 16:07:32 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once("WHAT/Lib.Http.php");
include_once("FDL/Lib.Dir.php");
include_once("FDL/freedom_util.php");
include_once("WGCAL/Lib.WGCal.php");
include_once("WGCAL/Lib.WgcalSync.php");
include_once("WGCAL/Class.WSyncDate.php");
include_once("WGCAL/Class.WSyncIds.php");

// event_update_sync.php?event_id=103825&date_debut=16/05/2005&time_debut=12:00:00&duration=60&access=P&priority=1&name=er&description=

$evid = GetHttpVars("event_id", -1);
$dstart = GetHttpVars("date_debut", "");
$tstart = GetHttpVars("time_debut", "");
$durat = GetHttpVars("duration", 0);
$access = GetHttpVars("access", "");
$prio = GetHttpVars("priority", "");
$title = GetHttpVars("name", "");
$descr = GetHttpVars("descr", "");
$debug = (GetHttpVars("debug", 0)==1?true:false);

$action = WSyncAuthent();
$dbdata = WSyncGetDataDb();
$dbadm = WSyncGetAdminDb();

$event = new Doc($dbdata, $evid);
if (!$event->isAlive()) return;


WSyncInitEvent($dbdata, $event, $title, $descr, $dstart, $tstart, $durat, $access, $prio );
$err = $event->Modify();
$err = $event->PostModify();
$event->AddComment(_("outlook modification"));

?>