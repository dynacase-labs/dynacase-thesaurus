<?php
// ---------------------------------------------------------------
// $Id: Class.VaultDiskFs.php,v 1.14 2007/01/16 10:04:53 eric Exp $
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

//
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
                                 max_size   int8,
                                 free_size   int8,
                                 subdir_cnt_bydir   int,
                                 subdir_deep   int,
                                 max_entries_by_dir   int,
                                 r_path varchar(2048)
                               );
           create sequence seq_id_vaultdiskfs%s start 10;";

  // --------------------------------------------------------------------
  function __construct($dbaccess, $id_fs='') {
  // --------------------------------------------------------------------
    $this->dbtable = sprintf($this->dbtable_tmpl, $this->specific);
    $this->sqlcreate = sprintf($this->sqlcreate_tmpl, $this->specific, $this->specific);
    $this->seq = sprintf($this->seq_tmpl, $this->specific);
    parent::__construct($dbaccess, $id_fs);

  }
 




  function createArch($maxsize,$path) {
    if (!is_dir($path)) $err=sprintf(_("%s directory not found"),$path);
    elseif (!is_writable($path)) $err=sprintf(_("%s directory not writable"),$path);
    if ($err=="") {
      $this->max_size         = $maxsize;
      $this->free_size        = $maxsize;
      $this->subdir_cnt_bydir = VAULT_MAXDIRBYDIR;
      $this->subdir_deep      = 1;
      $this->max_entries_by_dir = VAULT_MAXENTRIESBYDIR;
      $this->r_path = $path;
      $err=$this->Add();    
    }
    return $err;
  }

  // --------------------------------------------------------------------
  function PreInsert() {
  // --------------------------------------------------------------------
    if ($this->Exists( $this->r_path)) return (_("File System already exists"));
    $res = $this->exec_query("select nextval ('".$this->seq."')");
    $arr = $this->fetch_array(0);
    $this->id_fs = $arr["nextval"];
    return '';
  }

  // --------------------------------------------------------------------
  function Exists($path) {
  // --------------------------------------------------------------------
    $query = new QueryDb($this->dbaccess, $this->dbtable);
    $query->basic_elem->sup_where=array("r_path='".$path."'");
    $t = $query->Query(0,0,"TABLE");
    return ($query->nb > 0);
  }

  // --------------------------------------------------------------------
  function SetFreeFs($f_size, &$id_fs, &$id_dir, &$f_path) {
  // --------------------------------------------------------------------
    $id_fs = $id_dir = -1;
    $f_path = "";
    $query = new QueryDb($this->dbaccess, $this->dbtable);
    $query->basic_elem->sup_where=array("free_size>".$f_size);
    $t = $query->Query(0,1,"TABLE");
  
    if ($query->nb > 0) {
      $ifs = 0;
      $dirfound = FALSE;
      while(!$dirfound && ($ifs < $query->nb)) {
	$sd = new VaultDiskDir($this->dbaccess,'', $this->specific);
	$msg = $sd->SetFreeDir($t[$ifs]);
	if ($msg == '') $dirfound = TRUE;
	else $ifs++;
      }
      if ($dirfound) {
	$this->Select($t[0]["id_fs"]);
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
    $query = new QueryDb($this->dbaccess, $this->dbtable);
    $query->basic_elem->sup_where=array("id_fs=".$id_fs);
    $t = $query->Query(0,0,"TABLE");
    if ($query->nb > 0) {
      $sd = new VaultDiskDir($this->dbaccess,  $id_dir,$this->specific);
      if ($sd->IsAffected()) {
	$f_path = $t[0]["r_path"]."/".$sd->l_path;
      } else {
	return(_("no vault directory found"));
      }
    } else {
      return(_("no vault file system found"));
    }
    return '';
  }

  // --------------------------------------------------------------------
  function AddEntry($fs) {
  // --------------------------------------------------------------------
    $this->free_size = $this->free_size - $fs;
    $err=$this->Modify();
  }
 
  // --------------------------------------------------------------------
  function DelEntry($id_fs, $id_dir, $fs) {
  // --------------------------------------------------------------------
    DbObj::Select($id_fs);
    if ($this->IsAffected()) {
      $this->free_size = $this->free_size + $fs;
      $this->Modify();
      $sd = new VaultDiskDir($this->dbaccess,  $id_dir,$this->specific);
      if ($sd->IsAffected()) {
	$sd->DelEntry();
      } else {
	return(_("no vault directory found"));
      }
    } else {
      return(_("no vault file system found"));
    }
    return '';
  }
  
  // --------------------------------------------------------------------
  function Stats(&$s) {
  // --------------------------------------------------------------------
    $query = new QueryDb($this->dbaccess, $this->dbtable);
    $t = $query->Query(0,0,"TABLE");
    while ($query->nb>0 && (list($k,$v) = each($t))) {
      $s["fs$k"]["root_dir"] = $v["r_path"];
      $s["fs$k"]["allowed_size"] = $v["max_size"];
      $s["fs$k"]["free_size"] = $v["free_size"];
      $sd = new VaultDiskDir($this->dbacces, '',$this->specific);
      $s["fs$k"]["free_entries"] =  $sd->FreeEntries($v["id_fs"]);
      unset($sd);
    }
    return '';
  }

}
?>