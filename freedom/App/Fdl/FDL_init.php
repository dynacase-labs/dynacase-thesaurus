<?php
// ---------------------------------------------------------------
// $Id: FDL_init.php,v 1.1 2002/02/05 16:34:07 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/App/Fdl/Attic/FDL_init.php,v $
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

global $app_const;

$app_const= array(
  "INIT" => "yes",
  "VERSION" => "0.0.4",
  "FREEDOM_DB" => "host=localhost user=anakeen port=5432 dbname=freedom",
  "UPLOAD_MAX_FILE_SIZE" => "200000"
);

include_once("FDL/Class.DocFile.php");
include_once("FDL/Class.Dir.php");
include_once("FDL/Class.DocSearch.php");



$doc = new DocFile($app_const["FREEDOM_DB"]);
$doc->InitObjectAcl();
$doc = new Dir($app_const["FREEDOM_DB"]);
$doc->InitObjectAcl();
$doc = new DocSearch($app_const["FREEDOM_DB"]);
$doc->InitObjectAcl();


?>
