<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2005
 * @version $Id: check_version.php,v 1.1 2005/04/11 19:05:42 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WGCAL
 * @subpackage SYNC
 */
 /**
 */
include_once("WGCAL/Lib.WgcalSync.php");

WSyncAuthent();

header ("Content-Type: text/plain");
if ($version >= "2.0") print ("NOERROR\n");
else print ("ERROR\n");

?>

