<?php
// ---------------------------------------------------------------
// $Id: Class.VaultDiskDirStorage.php,v 1.3 2005/09/27 13:33:52 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/vault/Class/Class.VaultDiskDirStorage.php,v $
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
// $Log: Class.VaultDiskDirStorage.php,v $
// Revision 1.3  2005/09/27 13:33:52  eric
// correct missing ?>
//
// Revision 1.2  2005/07/01 09:11:19  eric
// PHP5
//
// Revision 1.1  2001/11/16 09:57:01  marc
// V0_0_1 Initial release, see CHANGELOG
//
//
// ---------------------------------------------------------------
include_once("VAULT/Class.VaultDiskDir.php");

Class VaultDiskDirStorage extends VaultDiskDir {

  function __construct($vault, $def='', $id_dir='') {
    parent::__construct($vault, "storage", $id_dir);
  }

}
?>