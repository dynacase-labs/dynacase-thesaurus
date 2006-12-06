<?php
/**
 * Retrieve and store file in Vault for unix fs
 *
 * @author Anakeen 2004
 * @version $Id: Class.VaultFileDisk.php,v 1.15 2006/12/06 11:12:13 eric Exp $
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
  function __construct($dbaccess,  $idf='') {
    // --------------------------------------------------------------------     
   
    $this->id_fs = '';
    $this->id_dir = '';
    DbObj::__construct($dbaccess, $idf);
    if ($this->storage == 1) {
      $this->fs = new VaultDiskFsStorage($dbaccess, $this->id_fs);
    } else {
      $this->fs = new VaultDiskFsCache($dbaccess, $this->id_fs);
    }
    $this->logger = new Log("", "vault", $this->name);
  }

  // --------------------------------------------------------------------
  function PreInsert() {
  // --------------------------------------------------------------------
    $res = $this->exec_query("select nextval ('".$this->seq."')");
    $arr = $this->fetch_array(0);
    $this->id_file = $arr["nextval"];
    return '';
  }

  // --------------------------------------------------------------------
  function fStat(&$fc, &$fv) {
  // --------------------------------------------------------------------
    $query = new QueryDb($this->dbaccess, $this->dbtable);
    $t = $query->Query(0,0,"TABLE");
    $fc = $query->nb;
    while ($fc>0 && (list($k,$v) = each($t))) $fv += $v["size"];
    unset($t);
    return '';
  }
    
  // --------------------------------------------------------------------
  function ListFiles(&$list) {
  // --------------------------------------------------------------------
    $query = new QueryDb($this->dbaccess, $this->dbtable);
    $list = $query->Query(0,0,"TABLE");
    $fc = $query->nb;
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

function seems_utf8($Str) {
 for ($i=0; $i<strlen($Str); $i++) {
  if (ord($Str[$i]) < 0x80) $n=0; # 0bbbbbbb
  elseif ((ord($Str[$i]) & 0xE0) == 0xC0) $n=1; # 110bbbbb
  elseif ((ord($Str[$i]) & 0xF0) == 0xE0) $n=2; # 1110bbbb
  elseif ((ord($Str[$i]) & 0xF0) == 0xF0) $n=3; # 1111bbbb
  else return false; # Does not match any model
  for ($j=0; $j<$n; $j++) { # n octets that match 10bbbbbb follow ?
   if ((++$i == strlen($Str)) || ((ord($Str[$i]) & 0xC0) != 0x80)) return false;
  }
 }
 return true;
}

  // --------------------------------------------------------------------
  function Store($infile, $public_access, &$idf) {
  // -------------------------------------------------------------------- 

    $this->size = filesize($infile);
    $msg = $this->fs->SetFreeFs($this->size, $id_fs, $id_dir, $f_path);
    if ($msg != '') {
      $this->logger->error("Can't find free entry in vault. [reason $msg]");
      return($msg);
    }
    $this->id_fs = $id_fs;
    $this->id_dir = $id_dir;
    $this->public_access = $public_access;
    $this->name = basename($infile);
    if ($this->seems_utf8( $this->name)) $this->name=utf8_decode($this->name);

    $msg = $this->Add();
    if ($msg != '') return($msg);
    
    $idf = $this->id_file;
    $f = vaultfilename($f_path, $infile, $this->id_file);
    if (! @copy($infile, $f)) {
      // Free entry
      return(_("Failed to copy $infile to $f"));
    }
    if (!chmod($f, VAULT_FMODE)) {
      $this->logger->warning("Can't change mode for $f");
    }
    if (!chown($f, HTTP_USER) || !chgrp($f, HTTP_USER)) {
      $this->logger->warning("Can't change owner for $f");
    }
    $this->fs->AddEntry($this->size);
    $this->logger->debug("File $infile stored in $f");
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
      @unlink($inf->path);
      $msg = $this->fs->DelEntry($this->id_fs, $this->id_dir, $inf->size);
      $this->Delete();
    }

    return $msg;
  }

  // --------------------------------------------------------------------
  function Save($infile, $public_access, $idf) {
  // -------------------------------------------------------------------- 


    $vf = new VaultFile($this->dbaccess);
    if ($vf->Show($idf, $info) == "") 
    {  
      $path = str_replace("//","/",$info->path);
    }
    
    $size=$this->size;
    $this->size = filesize($infile);
    $newsize=$this->size - $size;
    

   // Verifier s'il y a assez de places ???
   
   $this->public_access = $public_access;
   $this->name = basename($infile);

    
   $fd = fopen($path, "w+");

//    if (!unlink($path))
//	return("NOT UNLINK $path\n"); 
 
    $msg = $this->modify();
    if ($msg != '') return($msg);

    if (!copy($infile, $path)) {
      return("La copie du fichier $infile dans $path n'a pas r&eacute;ussi...\n");
    }
    $this->fs->select($this->id_fs);
    $this->fs->AddEntry($newsize - $size);
    $this->logger->debug("File $infile saved in $pathname");
    return "";
  }


} // End Class.VaultFileDisk.php 

?>