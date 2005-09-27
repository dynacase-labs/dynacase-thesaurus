<?php
// ---------------------------------------------------------------
// $Id: Class.VaultCache.php,v 1.3 2005/09/27 16:46:24 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/vault/Class/Attic/Class.VaultCache.php,v $
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
// ---------------------------------------------------------------
include_once("VAULT/Class.VaultFileDisk.php");

Class VaultCache extends VaultFileDisk {

  var $fields = array ( "id_file", 
			"id_fs", 
			"id_dir", 
			"public_access",
			"access_date",
                        "size",
			"name" );
  var $id_fields = array ("id_file");
  var $dbtable = "vaultcache";
  var $order_by="";
  var $sqlcreate = "  create table vaultcache  ( 
                                     id_file    int not null,
                                     primary key (id_file),
                                     id_fs int,
                                     id_dir int,
                                     public_access bool,
                                     access_date int,
                                     size int,
                                    name varchar(2048)
                               );";

  var $storage = 0;


  // --------------------------------------------------------------------
  function Insert($infile, $public_access, $idf) {
  // -------------------------------------------------------------------- 
    $this->id_fs = $this->fs->id_fs;
    $this->id_dir = $this->dir->id_dir;
    $this->public_access = $public_access;
    $this->name = basename($infile);
    $this->id_file = $idf;
    $this->Add();
    $f = $fs->r_path."/".$dir->l_path."/".$this->id_file;
    copy($infile, $f);
    chmod($f, $this->file_mode);
    chown($f, $this->u_owner);
    chgroup($f, $this->g_owner);
    $fs->AddEntry();
    $dir->AddEntry();
    return "";
  }

  // --------------------------------------------------------------------     
  function Access($idf) { 
  // --------------------------------------------------------------------     
    $this->access_time = time();
    $this->Modify();
  }


} // End Class.VaultCache.php 
?>