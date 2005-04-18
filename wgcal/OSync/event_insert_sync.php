<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: event_insert_sync.php,v 1.1 2005/04/18 15:39:30 marc Exp $
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

$s_date = GetHttpVars("start_date", -1);
$s_time = GetHttpVars("start_time", -1);
$name = utf8_decode(GetHttpVars("name", ""));
$descr = utf8_decode(GetHttpVars("description", ""));

$event = createDoc($dbdata, "CALEVENT");


// return event id
$err = $event->Add();
$err = $event->Modify();
print $event->id;

?>