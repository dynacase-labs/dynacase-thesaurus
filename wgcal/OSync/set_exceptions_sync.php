<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: set_exceptions_sync.php,v 1.2 2005/06/21 09:50:21 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once("Lib.WgcalSync.php");
include_once("FDL/Class.Doc.php");

$evid = GetHttpVars("id", -1);
$except = GetHttpVars("date", "");
echo "id = ".$evid."<br>";
echo "date = ".$except."<br>";
if ($evid==-1 || $except=="") return;


$action = WSyncAuthent();
$db = WSyncGetDataDb();
$event = new Doc($db, $evid);
if (!$event->IsAffected()) return;

$tocc = $event->getTValue("CALEV_EXCLUDEDATE");
$tnocc = array();
foreach ($tocc as $k => $v) {
    if (substr($v,0,11) != substr($except,0,11)) $tnocc[] = $v;
}
$tnocc[] = substr($except,0,11);
print_r2($tnocc);
$event->setValue("CALEV_EXCLUDEDATE",$tnocc );
$err = $event->Modify();
$err = $event->PostModify();

?>


