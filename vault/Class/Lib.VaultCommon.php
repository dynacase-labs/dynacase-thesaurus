<?php
// ---------------------------------------------------------------
// $Id: Lib.VaultCommon.php,v 1.4 2005/09/27 13:33:52 eric Exp $
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
// $Log: Lib.VaultCommon.php,v $
// Revision 1.4  2005/09/27 13:33:52  eric
// correct missing ?>
//
// Revision 1.3  2004/03/16 14:02:52  eric
// correction for extension file
//
// Revision 1.2  2002/08/06 16:51:03  marc
// 0.0.3-2, see ChangeLog
//
// Revision 1.1  2002/08/01 17:42:39  marc
// Version 0.0.3 release 1 see changelog
//
// ---------------------------------------------------------------

// ---------------------------------------------------------
function fileextension($filename, $ext="nop") {
  $te = explode(".", basename($filename));
  if (count($te)>1) $ext = $te[count($te)-1];
  return $ext;
}  

// ---------------------------------------------------------
function vaultfilename($fspath, $name, $id) {
  return $fspath."/".$id.".".fileextension($name);
}  


?>