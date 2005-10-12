<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: check_version.php,v 1.5 2005/10/12 07:33:49 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once("Lib.WgcalSync.php");

$action = WSyncAuthent();
if ($version >= $action->GetParam("WGCAL_SYNCVERSION","0")) print ("NOERROR\n");$version = GetHttpVars("version", "0");
header ("Content-Type: text/plain");
if ($version >= $action->GetParam("WGCAL_SYNCVERSION","0")) print ("NOERROR\n");
else print ("ERROR\n");

?>

