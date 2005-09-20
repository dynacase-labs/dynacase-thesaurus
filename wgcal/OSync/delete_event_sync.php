<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: delete_event_sync.php,v 1.5 2005/09/20 17:14:49 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once("Lib.WgcalSync.php");
include_once("FDL/Class.Doc.php");

$evid = GetHttpVars("event_id", -1);
if ($evid==-1) exit;

$action = WSyncAuthent();
$db = WSyncGetDataDb($action);
$ev = new_Doc($db, $evid);
if ($ev->IsAffected()) {
  $err = $ev->Delete();
  $err = $event->postDelete();
} 
?>
