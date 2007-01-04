<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: get_lastsyncdate_sync.php,v 1.5 2007/01/04 16:40:37 caroline Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once("Lib.WgcalSync.php");
include_once("Class.WSyncDate.php");

$action = WSyncAuthent();

$db = WSyncGetAdminDb();
$syncdate = new WSyncDate($db, $action->parent->user->fid);

if ($syncdate->server_date=="") {
$syncdate->server_date=0;
}

if ( $syncdate->outlook_date=="") {
$syncdate->outlook_date=0;
}

print WSyncTs2Outlook($syncdate->server_date)." ".WSyncTs2Outlook($syncdate->outlook_date);

?>
