<?php
/**
 * Utilities functions for freedom
 *
 * @author Anakeen 2004
 * @version $Id: Lib.Util.php,v 1.1 2004/08/05 09:47:20 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
function newFreeVaultFile($dbaccess) {
  include_once("VAULT/Class.VaultFile.php");
  return new VaultFile($dbaccess, strtoupper(getDbName($dbaccess)));
}
function getGen($dbaccess) {
  if (getDbName($dbaccess) != "freedom") return "GEN/".strtoupper(getDbName($dbaccess));
  return "GEN";
}
?>