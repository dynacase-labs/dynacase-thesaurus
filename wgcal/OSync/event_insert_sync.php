<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: event_insert_sync.php,v 1.5 2005/06/21 09:50:21 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once("FDL/Lib.Dir.php");
include_once("FDL/freedom_util.php");
include_once("WGCAL/Lib.WGCal.php");
include_once("Lib.WgcalSync.php");
include_once("Class.WSyncDate.php");
include_once("Class.WSyncIds.php");

global $action;
$action = WSyncAuthent();

$debug = (GetHttpVars("debug", 0)==1?true:false);

$dbdata = WSyncGetDataDb($action);
$dbadm = WSyncGetAdminDb($action);

$event = createDoc($dbdata, "CALEVENT");


WSyncInitEvent($dbdata,
	       $event, 
	       GetHttpVars("name"), 
	       GetHttpVars("description"),
	       GetHttpVars("start_date", ""),
	       GetHttpVars("start_time", ""),
	       GetHttpVars("duration", 0),
	       GetHttpVars("access","P"),
	       GetHttpVars("priority") );
	       

// return event id
$err = $event->Add();
$err = $event->PostModify();
$err = $event->Modify();

WSyncUpdateIds($event->id, GetHttpVars("olookid",0));

$event->AddComment(_("created from outlook")." [".GetHttpVars("olookid",0)."]");

print $event->id;
// if ($debug) print_r2($event);
?>
