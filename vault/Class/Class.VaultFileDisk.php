<?php
/**
 * Retrieve and store file in Vault for unix fs
 *
 * @author Anakeen 2004
 * @version $Id: Class.VaultFileDisk.php,v 1.6 2004/06/30 07:32:06 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package VAULT
 */
 /**
 */

include_once("VAULT/Class.VaultDiskFsStorage.php");
include_once("VAULT/Class.VaultDiskFsCache.php");
include_once("VAULT/Class.VaultDiskDirStorage.php");
include_once("VAULT/Lib.VaultCommon.php");

Class VaultFileDisk extends DbObj {

  // --------------------------------------------------------------------
  function VaultFileDisk($vault, $arch='', $idf='') {
    // --------------------------------------------------------------------     
    $this->vault = $vault;
    $this->arch = $arch;
    $this->id_fs = '';
    $this->id_dir = '';
    DbObj::DbObj($vault->dbaccess, $idf);
    if ($this->storage == 1) {
      $this->fs = new VaultDiskFsStorage($this->vault, $this->arch, $this->id_fs);
    } else {
      $this->fs = new VaultDiskFsCache($this->vault, $this->arch, $this->id_fs);
    }
  }

  // --------------------------------------------------------------------
  function PreInsert() {
  // --------------------------------------------------------------------
    $res = $this->exec_query("select nextval ('".$this->seq."')");
    $arr = $this->fetch_array (0);
    $this->id_file = $arr[0];
    return '';
  }

  // --------------------------------------------------------------------
  function fStat(&$fc, &$fv) {
  // --------------------------------------------------------------------
    $query = new QueryDb($this->vault, $this->dbtable);
    $t = $query->Query(0,0,"TABLE");
    $fc = $query->nb;
    while ($fc>0 && (list($k,$v) = each($t))) $fv += $v["size"];
    unset($t);
    return '';
  }
    
  // --------------------------------------------------------------------
  function ListFiles(&$list) {
  // --------------------------------------------------------------------
    $query = new QueryDb($this->vault, $this->dbtable);
    $t = $query->Query(0,0,"TABLE");
    $fc = $query->nb;
    while ($fc>0 && (list($k,$v) = each($t))) {
      $list[$k]["name"] = $v["name"];
      $list[$k]["size"] = $v["size"];
      $list[$k]["access"] = ($v["public_access"]?"PUBLIC":"RESTRICTED");
    }
    unset($t);
    return $fc;
  }

  // --------------------------------------------------------------------
  function Stats(&$s) {
  // --------------------------------------------------------------------
    $this->fs->Stats($s);
    $this->fStat($file_count, $vol);
    $s["general"]["file_count"] = $file_count;
    $s["general"]["file_size"] =  $vol;
    return '';
  }


  // --------------------------------------------------------------------
  function Store($infile, $public_access, &$idf) {
  // -------------------------------------------------------------------- 

    $this->size = filesize($infile);
    $msg = $this->fs->SetFreeFs($this->size, $id_fs, $id_dir, $f_path);
    if ($msg != '') {
      $this->vault->logger->error("Can't find free entry in vault. [reason $msg]");
      return($msg);
    }
    $this->id_fs = $id_fs;
    $this->id_dir = $id_dir;
    $this->public_access = $public_access;
    $this->name = basename($infile);

    $msg = $this->Add();
    if ($msg != '') return($msg);
    
    $idf = $this->id_file;
    $f = vaultfilename($f_path, $infile, $this->id_file);
    if (! @copy($infile, $f)) {
      // Free entry
      return(_("Failed to copy $infile to $f"));
    }
    if (!chmod($f, $this->vault->f_mode)) {
      $this->vault->logger->warning("Can't change mode for $f");
    }
    if (!chown($f, $this->vault->u_owner) || !chgrp($f, $this->vault->g_owner)) {
      $this->vault->logger->warning("Can't change owner for $f");
    }
    $this->fs->AddEntry($this->size);
    $this->vault->logger->debug("File $infile stored in $f");
    return "";
  }

  // --------------------------------------------------------------------     
  function Show($id_file, &$f_infos) { 
  // --------------------------------------------------------------------     
    $this->id_file = -1;
    $msg = DbObj::Select($id_file);
    if ($this->id_file!=-1) {
      $this->fs->Show($this->id_fs, $this->id_dir, $f_path);
      $f_infos->name = $this->name;
      $f_infos->size = $this->size;
      $f_infos->public_access = $this->public_access;
      $f_infos->path = vaultfilename($f_path, $this->name, $id_file);
      return '';
    } else {
      return(_("file does not exist in vault"));
    }
  }

  // --------------------------------------------------------------------     
  function Destroy($id) { 
  // --------------------------------------------------------------------     
    $msg = $this->Show($id, $inf);
    if ($msg == '' ) {
      unlink($inf->path);
      $msg = $this->fs->DelEntry($this->id_fs, $this->id_dir, $inf->size);
      $this->Delete();
    }

    return $msg;
  }

  // --------------------------------------------------------------------
  function Save($infile, $public_access, $idf, $pathname) {
  // -------------------------------------------------------------------- 


    $vf = new VaultFile($dbaccess, "FREEDOM");
    if ($vf -> Show ($idf, $info) == "") 
    {  
     $path = $info->path;
    }
    
    $this->size = filesize($infile);

   // Verifier s'il y a assez de places ???
   
   $this->public_access = $public_access;
   $this->name = basename($infile);

   $path = str_replace("//","/",$pathname);
    
   $fd = fopen($path, "w+");

//    if (!unlink($path))
//	return("NOT UNLINK $path\n"); 
 

  if (!copy($infile, $path)) {
    return("La copie du fichier $infile dans $path n'a pas r&eacute;ussi...\n");
  }

    if (!chmod($pathname, $this->vault->f_mode)) {
      $this->vault->logger->warning("Can't change mode for $pathname");
    }
    if (!chown($pathname, $this->vault->u_owner) || !chgrp($pathname, $this->vault->g_owner)) {
      $this->vault->logger->warning("Can't change owner for $pathname");
    }

    $this->fs->AddEntry(size);
    $this->vault->logger->debug("File $infile saved in $pathname");
    return "";
  }


} // End Class.VaultFileDisk.php 

