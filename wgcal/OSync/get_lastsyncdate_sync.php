<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: get_lastsyncdate_sync.php,v 1.1 2005/04/11 19:05:42 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once("WGCAL/Lib.WgcalSync.php");
include_once("WGCAL/Class.WSyncDate.php");

$ctx = WSyncAuthent();

$db = WSyncGetAdminDb($ctx);
$syncdate = new WSyncDate($db, $ctx->user->id);

print $syncdate->server_date." ".$syncdate->outlook_date;

?>
