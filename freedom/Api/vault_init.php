<?php
/**
 * Initialisation of the FREEDOM VAULT based on the VAULT/FREEDOM.vault file
 *
 * create all sub-directories where files will be inserted by the VAULT application
 * @author Anakeen 2000 
 * @version $Id: vault_init.php,v 1.2 2003/08/18 15:47:04 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WSH
 */
 /**
 */
 
include_once("VAULT/Class.VaultFile.php");
   
$appl = new Application();
$appl->Set("FDL",	   $core);

$dbaccess=$appl->GetParam("FREEDOM_DB");

$vf = new VaultFile($dbaccess, "FREEDOM");
?>