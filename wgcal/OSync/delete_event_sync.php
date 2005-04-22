<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: delete_event_sync.php,v 1.2 2005/04/22 16:03:29 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once("WGCAL/Lib.WgcalSync.php");
include_once("FDL/Class.Doc.php");

$evid = GetHttpVars("event_id", -1);
if ($evid==-1) exit;

$ctx = WSyncAuthent();
$db = WSyncGetDataDb($ctx);
$ev = new Doc($db, $evid);
if ($ev->IsAffected()) {
  $err = $ev->Delete();
} 
?>