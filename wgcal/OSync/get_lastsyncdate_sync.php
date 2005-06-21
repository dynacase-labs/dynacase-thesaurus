<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: get_lastsyncdate_sync.php,v 1.4 2005/06/21 09:50:21 marc Exp $
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

print WSyncTs2Outlook($syncdate->server_date)." ".WSyncTs2Outlook($syncdate->outlook_date);

?>
