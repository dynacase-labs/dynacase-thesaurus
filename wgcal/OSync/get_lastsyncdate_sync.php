<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: get_lastsyncdate_sync.php,v 1.2 2005/04/18 15:39:30 marc Exp $
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

print WSyncTs2Outlook($syncdate->server_date)." ".WSyncTs2Outlook($syncdate->outlook_date);

?>
