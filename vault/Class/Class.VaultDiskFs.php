<?php
// ---------------------------------------------------------------
// $Id: Class.VaultDiskFs.php,v 1.1 2001/11/16 09:57:01 marc Exp $
// $Source: /home/cvsroot/anakeen/freedom/vault/Class/Class.VaultDiskFs.php,v $
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
// $Log: Class.VaultDiskFs.php,v $
// Revision 1.1  2001/11/16 09:57:01  marc
// V0_0_1 Initial release, see CHANGELOG
//
//
// ---------------------------------------------------------------
include_once("Class.QueryDb.php");
include_once("Class.DbObj.php");
include_once("VAULT/Class.VaultDiskDir.php");

Class VaultDiskFs extends DbObj {

  var $fields = array ( "id_fs", 
			"max_size",
			"free_size",
			"subdir_cnt_bydir",
			"subdir_deep",
			"max_entries_by_dir", 
			"r_path" );
  var $id_fields = array ("id_fs");
  var $dbtable_tmpl = "vaultdiskfs%s";
  var $order_by="";
  var $seq_tmpl="seq_id_vaultdiskfs%s";
  var $sqlcreate_tmpl = "
           create table vaultdiskfs%s  ( id_fs     int not null,
                                 primary key (id_fs),
                                 max_size   int,
                                 free_size   int,
                                 subdir_cnt_bydir   int,
                                 subdir_deep   int,
                                 max_entries_by_dir   int,
                                 r_path varchar(2048)
                               );
           create sequence seq_id_vaultdiskfs%s start 10;";

  // --------------------------------------------------------------------
  function VaultDiskFs($vault, $arch='', $id_fs='') {
  // --------------------------------------------------------------------
    $this->dbtable = sprintf($this->dbtable_tmpl, $this->specific);
    $this->sqlcreate = sprintf($this->sqlcreate_tmpl, $this->specific, $this->specific);
    $this->seq = sprintf($this->seq_tmpl, $this->specific);
    $this->vault = $vault;
    DbObj::DbObj($this->vault->dbaccess, $id_fs);
    $this->arch = $arch;
    $this->InitArch();
  }

  // --------------------------------------------------------------------
  function CreateDir($fs, $dir, $level) {
  // --------------------------------------------------------------------
    if ($level <= $fs["subdir_deep"]) {
      $level++;
      for ($id=1; $id<=$fs["subdir_cnt_bydir"]; $id++) {
	$sdir = $dir.$id."/";
	$this->vault->logger->info("Creating subdir [".$sdir."]");
	mkdir($this->r_path."/".$sdir, $this->vault->d_mode);
	chown($this->r_path."/".$sdir, $this->vault->u_owner);
	chgrp($this->r_path."/".$sdir, $this->vault->g_owner);
	$sd = new VaultDiskDir($this->vault, $this->specific);
	$sd->id_fs = $this->id_fs;
	$sd->l_path = $sdir;
	$sd->fs = $this;
	$sd->free_entries = $fs["max_entries_by_dir"];
	$sd->Add();
	$this->CreateDir($fs, $sdir, $level);
      }
      $level++;
   }
  }


  // --------------------------------------------------------------------
  function InitArch() {
  // --------------------------------------------------------------------
    if (!is_array($this->arch)) return;
    while (list($k, $v) = each($this->arch)) {
      $level = 1;
      if (!file_exists($v["r_path"])) {
	$this->vault->logger->info("Creating File System [".$v["r_path"]."]");
	mkdir($v["r_path"], $this->vault->d_mode);
	chown($v["r_path"], $this->vault->u_owner);
	chgrp($v["r_path"], $this->vault->g_owner);
	$this->max_size         = $v["max_size"];
	$this->free_size        = $v["max_size"];
	$this->subdir_cnt_bydir = $v["subdir_cnt_bydir"];
	$this->subdir_deep      = $v["subdir_deep"];
	$this->max_entries_by_dir = $v["max_entries_by_dir"];
	$this->r_path = $v["r_path"];
	$this->Add();
	$this->CreateDir($v, "", $level);
      }
    }
  }

  // --------------------------------------------------------------------
  function PreInsert() {
  // --------------------------------------------------------------------
    if ($this->Exists( $this->r_path)) return (_("File System already exists"));
    $res = $this->exec_query("select nextval ('".$this->seq."')");
    $arr = $this->fetch_array (0);
    $this->id_fs = $arr[0];
    return '';
  }

  // --------------------------------------------------------------------
  function Exists($path) {
  // --------------------------------------------------------------------
    $query = new QueryDb($this->vault, $this->dbtable);
    $query->basic_elem->sup_where=array("r_path='".$path."'");
    $t = $query->Query();
    return ($query->nb > 0);
  }

  // --------------------------------------------------------------------
  function SetFreeFs($f_size, &$id_fs, &$id_dir, &$f_path) {
  // --------------------------------------------------------------------
    $id_fs = $id_dir = -1;
    $f_path = "";
    $query = new QueryDb($this->vault, $this->dbtable);
    $query->basic_elem->sup_where=array("free_size>".$f_size);
    $t = $query->Query();
    if ($query->nb > 0) {
      $ifs = 0;
      $dirfound = FALSE;
      while(!$dirfound && ($ifs < $query->nb)) {
	$sd = new VaultDiskDir($this->vault, $this->specific);
	$msg = $sd->SetFreeDir($t[$ifs]->id_fs);
	if ($msg == '') $dirfound = TRUE;
	else $ifs++;
      }
      if ($dirfound) {
	$this->Select($t[0]->id_fs);
	$id_fs = $this->id_fs;
	$id_dir = $sd->id_dir;
	$f_path = $this->r_path."/".$sd->l_path;
      } else {
	return($msg);
      }
      unset($t);
   } else {
      return(_("no empty vault file system found"));
    }
    return "";
  }

  // --------------------------------------------------------------------
  function Show($id_fs, $id_dir, &$f_path) {
  // --------------------------------------------------------------------
    $query = new QueryDb($this->vault, $this->dbtable);
    $query->basic_elem->sup_where=array("id_fs=".$id_fs);
    $t = $query->Query();
    if ($query->nb > 0) {
      $sd = new VaultDiskDir($this->vault, $this->specific, $id_dir);
      if ($sd->IsAffected()) {
	$f_path = $t[0]->r_path."/".$sd->l_path;
      } else {
	return(_("no vault directory found"));
      }
    } else {
      return(_("no vault file system found"));
    }
    return '';
  }

  // --------------------------------------------------------------------
  function AddEntry($f) {
  // --------------------------------------------------------------------
    $this->free_size = $this->max_size - filesize($f);
    $this->Modify();
  }
 
  // --------------------------------------------------------------------
  function DelEntry($f) {
  // --------------------------------------------------------------------
    $this->free_size = $this->max_size + filesize($f);
    $this->Modify();
    return '';
  }
  
  // --------------------------------------------------------------------
  function Stats(&$s) {
  // --------------------------------------------------------------------
    $query = new QueryDb($this->vault, $this->dbtable);
    $t = $query->Query();
    while ($query->nb>0 && (list($k,$v) = each($t))) {
      $s["fs$k"]["root_dir"] = $v->r_path;
      $s["fs$k"]["allowed_size"] = $v->max_size;
      $s["fs$k"]["free_size"] = $v->free_size;
      $sd = new VaultDiskDir($this->vault, $this->specific);
      $s["fs$k"]["free_entries"] =  $sd->FreeEntries($v->id_fs);
      unset($sd);
    }
    return '';
  }

}
