<?php
/**
 * Create new Vault FS
 *
 * @author Anakeen 2006
 * @version $Id: vault_createfs.php,v 1.3 2006/12/21 18:01:05 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package VAULT
 * @subpackage 
 */
 /**
 */


include_once("VAULT/Class.VaultDiskStorage.php");
include_once("VAULT/Class.VaultDiskFsStorage.php");
include_once("VAULT/Class.VaultFile.php");
include_once("FDL/Class.DocVaultIndex.php");
// -----------------------------------
function vault_createfs(&$action) {

  // GetAllParameters
  
  $unit = GetHttpVars("unitsize");
  $size = intval(GetHttpVars("size"));
  $dirname = GetHttpVars("directory");
 

  switch ($unit) {
  case "Kb": $size_in_bytes=$size*1024;
    break;
  case "Mb": $size_in_bytes=$size*1024*1024;
    break;
  case "Gb": $size_in_bytes=$size*1024*1024*1024;
    break;
  case "Tb": $size_in_bytes=$size*1024*1024*1024*1024;
    break;
  }
  $dbaccess = $action->GetParam("FREEDOM_DB");



  if (!is_dir($dirname)) $err=sprintf(_("%s directory not found"),$dirname);
  if ($err=="") {
    if (!is_writable($dirname)) $err=sprintf(_("%s directory not writable"),$dirname);
    if ($err=="") {
      $telts=scandir($dirname);
      if (count($telts)>2) $err=sprintf(_("%s directory not empty"),$dirname);

      if ($err=="") {
	
	$vf=new VaultFile($dbaccess);
	
	//  print_r2($vf);
	$q=new QueryDb($dbaccess,"VaultDiskFsStorage"); 
	$q->AddQuery("r_path='".pg_escape_string(trim($dirname))."'");
	$l=$q->Query(0,0,"TABLE");

	if ($q->nb==0) {
	  $vf->storage->fs->createArch($size_in_bytes,$dirname);
	  $action->AddWarningMsg(sprintf(_("create vault %s"), $dirname));
	}else {
	  $err= sprintf(_("vault already created %s: aborted\n"),$dirname);    
	}
      }
    }
  }
  
  if ($err!="") $action->AddWarningMsg($err);
  redirect($action,"VAULT","VAULT_VIEW",$action->GetParam("CORE_STANDURL"));
  
}


?>
