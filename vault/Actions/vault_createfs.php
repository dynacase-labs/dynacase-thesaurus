<?php
/**
 * Create new Vault FS
 *
 * @author Anakeen 2006
 * @version $Id: vault_createfs.php,v 1.1 2006/11/30 17:39:22 eric Exp $
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



  if (!is_dir($dirname)) $action->exitError(sprintf(_("%s directory not found"),$dirname));
  if (!is_writable($dirname)) $action->exitError(sprintf(_("%s directory not writable"),$dirname));
  $telts=scandir($dirname);
  if (count($telts)>2) $action->exitError(sprintf(_("%s directory not empty"),$dirname));

  $nfiles=$size_in_bytes/1024;
  $max_entries_by_dir=1500;
  $ndir=intval($nfiles/$max_entries_by_dir);
  $subdir_cnt_bydir=100;
  $subdir_deep=round(log($ndir)/log($subdir_cnt_bydir)+1);
  print "ndir: $ndir<bR>";
  $p=1;$nr=0;
  while ($nr<$ndir) {
    $nr+=pow($subdir_cnt_bydir,$p);    
    print "$p)nr: $nr<bR>";
    $p++;
  }

  print "nr: $nr<bR>";
  $vf=new VaultFile($dbaccess);
  $vf->arch=array("NEW"=>array("max_size" => $size_in_bytes,
			       "subdir_cnt_bydir" => $subdir_cnt_bydir,
			       "subdir_deep" => $p-1,
			       "max_entries_by_dir" =>$max_entries_by_dir ,
			       "r_path" => $dirname));
  //$vf->storage->initArch();
  

  print_r2($vf->arch);
    print "found $dirname";
    print_r2(stat($dirname));
    print_r2(scandir($dirname));
    print_r2(is_writable($dirname));
  

}


?>
