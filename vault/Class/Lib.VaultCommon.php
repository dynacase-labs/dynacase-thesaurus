<?php
// ---------------------------------------------------------------
// $Id: Lib.VaultCommon.php,v 1.6 2006/10/13 13:44:37 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/vault/Class/Lib.VaultCommon.php,v $
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


// ---------------------------------------------------------
function fileextension($filename, $ext="nop") {
  $te = explode(".", basename($filename));
  if (count($te)>1) $ext = $te[count($te)-1];
  return $ext;
}  

// ---------------------------------------------------------
function vaultfilename($fspath, $name, $id) {
  return str_replace('//','/',$fspath."/".$id.".".fileextension($name));
}  


?>