<?php
/**
 * Initialisation of the FREEDOM VAULT based on the VAULT/FREEDOM.vault file
 *
 * create all sub-directories where files will be inserted by the VAULT application
 * @author Anakeen 2000 
 * @version $Id: vault_init.php,v 1.3 2004/08/05 09:47:20 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WSH
 */
 /**
 */
 
include_once("VAULT/Class.VaultFile.php");
include_once("FDL/Lib.Util.php");
   
$appl = new Application();
$appl->Set("FDL",	   $core);

$dbaccess=$appl->GetParam("FREEDOM_DB");
$dbname=getDbName($dbaccess);
$vf = newFreeVaultFile($dbaccess);
?>