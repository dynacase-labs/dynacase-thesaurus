<?php
// ---------------------------------------------------------------
// $Id: Class.VaultFileDisk.php,v 1.1 2001/11/16 09:57:01 marc Exp $
// $Source: /home/cvsroot/anakeen/freedom/vault/Class/Attic/Class.VaultFileDisk.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2001
// O*O  Anakeen development team
//  O   dev@anakeen.com
// ---------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------
// $Log: Class.VaultFileDisk.php,v $
// Revision 1.1  2001/11/16 09:57:01  marc
// V0_0_1 Initial release, see CHANGELOG
//
//
// ---------------------------------------------------------------
include_once("VAULT/Class.VaultDiskFsStorage.php");
include_once("VAULT/Class.VaultDiskFsCache.php");
include_once("VAULT/Class.VaultDiskDirStorage.php");
include_once("VAULT/Class.VaultDiskDirCache.php");

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
    $t = $query->Query();
    $fc = $query->nb;
    while ($fc>0 && (list($k,$v) = each($t))) $fv += $v->size;
    unset($t);
    return '';
  }
    
  // --------------------------------------------------------------------
  function ListFiles(&$list) {
  // --------------------------------------------------------------------
    $query = new QueryDb($this->vault, $this->dbtable);
    $t = $query->Query();
    $fc = $query->nb;
    while ($fc>0 && (list($k,$v) = each($t))) {
      $list[$k]["name"] = $v->name;
      $list[$k]["size"] = $v->size;
      $list[$k]["access"] = ($v->public_access?"PUBLIC":"RESTRICTED");
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
    $f = $f_path."F-".$this->id_file;
    if (!copy($infile, $f)) {
      // Free entry
      return(_("Failed to copy $infile to $f"));
    }
    if (!chmod($f, $this->vault->f_mode)) {
      $this->vault->logger->warning("Can't change mode for $f");
    }
    if (!chown($f, $this->vault->u_owner) || !chgrp($f, $this->vault->g_owner)) {
      $this->vault->logger->warning("Can't change owner for $f");
    }
    $this->fs->AddEntry($f);
    $this->vault->logger->info("File $infile stored in $f");
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
      $f_infos->path = $f_path."F-".$id_file;
      return '';
    } else {
      return(_("file does not exist in vault"));
    }
  }

  // --------------------------------------------------------------------     
  function Delete() { 
  // --------------------------------------------------------------------     
    unlink($this->f_path);
    $this->fs->DelEntry();
    $this->dir->DelEntry();
  }


} // End Class.VaultFileDisk.php 

