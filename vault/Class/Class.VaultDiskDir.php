<?php
// ---------------------------------------------------------------
// $Id: Class.VaultDiskDir.php,v 1.1 2001/11/16 09:57:01 marc Exp $
// $Source: /home/cvsroot/anakeen/freedom/vault/Class/Class.VaultDiskDir.php,v $
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
// $Log: Class.VaultDiskDir.php,v $
// Revision 1.1  2001/11/16 09:57:01  marc
// V0_0_1 Initial release, see CHANGELOG
//
//
// ---------------------------------------------------------------
include_once("Class.QueryDb.php");

Class VaultDiskDir extends DbObj {

  var $fields = array ( "id_dir", "id_fs", "free_entries", "l_path" );
  var $id_fields = array ("id_dir");
  var $dbtable_tmpl = "vaultdiskdir%s";
  var $order_by="";
  var $seq_tmpl="seq_id_vaultdiskdir%s";
  var $sqlcreate_tmpl = "
           create table vaultdiskdir%s  ( id_dir     int not null,
                                 primary key (id_dir),
				 id_fs          int,
				 free_entries   int,
                                 l_path varchar(2048)
                               );
           create sequence seq_id_vaultdiskdir%s start 10";

  // --------------------------------------------------------------------
  function VaultDiskDir($vault, $def='', $id_dir='') {
  // --------------------------------------------------------------------
    $this->specific = $def;
    $this->dbtable = sprintf($this->dbtable_tmpl, $this->specific);
    $this->sqlcreate = sprintf($this->sqlcreate_tmpl, $this->specific, $this->specific);
    $this->seq = sprintf($this->seq_tmpl, $this->specific);
    $this->vault = $vault;
    DbObj::DbObj($this->vault->dbaccess, $id_dir);
  }

  // --------------------------------------------------------------------
  function SetFreeDir($id_fs) {
  // --------------------------------------------------------------------
    $query = new QueryDb($this->vault, $this->dbtable);
    $query->basic_elem->sup_where=array("id_fs=".$id_fs, 
					"free_entries>0");
    $t = $query->Query();
    if ($query->nb > 0) {
      $this->Select($t[0]->id_dir);
      unset($t);
      $this->free_entries--;
      $this->Modify();
    } else {
      $this->vault->logger->error("Vault dirs full");
      return(_("no empty vault dir found"));
      $this = FALSE;
    }
    return "";
  }

  // --------------------------------------------------------------------
  function PreInsert() {
  // --------------------------------------------------------------------
    if ($this->Exists( $this->l_path, $this->id_fs)) return (_("Directory already exists"));
    $res = $this->exec_query("select nextval ('".$this->seq."')");
    $arr = $this->fetch_array (0);
    $this->id_dir = $arr[0];
    return '';
  }

  // --------------------------------------------------------------------
  function Exists($path, $id_fs) {
  // --------------------------------------------------------------------
    $query = new QueryDb($this->vault, $this->dbtable);
    $query->basic_elem->sup_where=array("l_path='".$path."'", "id_fs=".$id_fs);
    $t = $query->Query();
    return ($query->nb > 0);
  }

  // --------------------------------------------------------------------
  function FreeEntries($id_fs) {
  // --------------------------------------------------------------------
    $free_entries = 0;
    $query = new QueryDb($this->vault, $this->dbtable);
    $query->basic_elem->sup_where=array("free_entries>0", "id_fs=".$id_fs);
    $t = $query->Query();
    while ($query->nb>0 && (list($k,$v) = each($t))) $free_entries += $v->free_entries;
    unset($t);
    return ($free_entries);
  }

}






