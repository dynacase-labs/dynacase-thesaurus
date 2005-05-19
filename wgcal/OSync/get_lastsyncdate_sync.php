<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: get_lastsyncdate_sync.php,v 1.3 2005/05/19 16:01:22 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once("WGCAL/Lib.WgcalSync.php");
include_once("WGCAL/Class.WSyncDate.php");

$action = WSyncAuthent();

$db = WSyncGetAdminDb();
$syncdate = new WSyncDate($db, $action->parent->user->fid);

print WSyncTs2Outlook($syncdate->server_date)." ".WSyncTs2Outlook($syncdate->outlook_date);

?>
