<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: delete_event_sync.php,v 1.1 2005/04/11 19:05:42 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once("WGCAL/Lib.WgcalSync.php");

$ctx = WSyncAuthent();
$db = WSyncDateDb($ctx);
$ev = new Doc($db, $evid);
if ($ev->IsAffected()) $ev->Delete();
?>