<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: check_version.php,v 1.3 2005/05/19 16:01:22 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once("WGCAL/Lib.WgcalSync.php");

$action = WSyncAuthent();

header ("Content-Type: text/plain");
if ($version >= $action->GetParam("WGCAL_SYNCVERSION","0")) print ("NOERROR\n");
else print ("ERROR\n");

?>

