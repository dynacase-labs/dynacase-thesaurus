<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: check_version.php,v 1.2 2005/04/22 16:03:29 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once("WGCAL/Lib.WgcalSync.php");

$ctx = WSyncAuthent();

header ("Content-Type: text/plain");
if ($version >= $ctx->GetParam("WGCAL_SYNCVERSION","0")) print ("NOERROR\n");
else print ("ERROR\n");

?>

