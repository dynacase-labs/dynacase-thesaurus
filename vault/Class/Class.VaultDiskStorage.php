<?php
// ---------------------------------------------------------------
// $Id: Class.VaultDiskStorage.php,v 1.4 2007/02/19 16:25:40 marc Exp $
// $Source: /home/cvsroot/anakeen/freedom/vault/Class/Class.VaultDiskStorage.php,v $
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
// ---------------------------------------------------------------
include_once("VAULT/Class.VaultFileDisk.php");

Class VaultDiskStorage extends VaultFileDisk {

  var $fields = array ( "id_file", 
			"id_fs", 
			"id_dir", 
			"public_access",
			"size",
			"name",
			
			"mime_t",         // file mime type text
			"mime_s",         // file mime type system

			"cdate",        // creation date
			"mdate",        // modification date
			"adate",        // access date

			"teng_state",     // Transformation Engine state
			"teng_lname",     // Transformation Engine logical name (VIEW, THUMBNAIL, ....)
			"teng_idfs",      // Transformation Engine source file id
			
			);
  var $id_fields = array ("id_file");
  var $dbtable = "vaultdiskstorage";
  var $seq = "seq_id_vaultdiskstorage";
  var $sqlcreate = "create table vaultdiskstorage  ( 
                                     id_file       int not null, primary key (id_file),
                                     id_fs         int,
                                     id_dir        int,
                                     public_access bool,
                                     size int,
                                     name varchar(2048),

                                     mime_t           text DEFAULT '',
                                     mime_s           text DEFAULT '',

                                     cdate            timestamp DEFAULT null,
                                     mdate            timestamp DEFAULT null,
                                     adate            timestamp DEFAULT null,
 
                                     teng_state       int DEFAULT 0,
                                     teng_lname       text DEFAULT '',
                                     teng_idfs        int DEFAULT -1

                               );
           create sequence seq_id_vaultdiskstorage start 10;";
  
  var $storage = 1;


} // End Class.VaultDisk.php 
?>