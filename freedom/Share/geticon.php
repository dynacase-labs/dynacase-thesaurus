<?php
// ---------------------------------------------------------------
// $Id: geticon.php,v 1.1 2002/09/24 15:30:09 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Share/geticon.php,v $
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


include_once("FDL/exportfile.php");


$vaultid = GetHttpVars("vaultid",0);
$$mimetype = GetHttpVars("$$mimetype","image");

$dbaccess = "host=localhost user=anakeen port=5432 dbname=freedom";
$vf = new VaultFile($dbaccess, "FREEDOM");

  if ($vf -> Retrieve ($vaultid, $info) != "") {    
  } else
    {
      //Header("Location: $url");
      if (( $info->public_access)) {
	Http_DownloadFile($info->path, $info->name, $mimetype);
	
      } else {
	Http_DownloadFile("FREEDOM/Images/doc.gif", "unknow", "image/gif");
      }
    }

?>
