<?php
// ---------------------------------------------------------------
// $Id: Class.PDir.php,v 1.2 2002/10/31 08:09:23 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Class.PDir.php,v $
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
$CLASS_DIR_PHP = '$Id: Class.PDir.php,v 1.2 2002/10/31 08:09:23 eric Exp $';


include_once("FDL/Class.Doc.php");


Class PDir extends Doc
{
    // --------------------------------------------------------------------
  //---------------------- OBJECT CONTROL PERMISSION --------------------
  
  var $obj_acl = array (
			array(
			      "name"		=>"view",
			      "description"	=>"view folder information", # N_("view folder")
			      "group_default"       =>"Y"),
			array(
			      "name"               =>"edit",
			      "description"        =>"edit folder information"),# N_("edit folder")
			array(
			      "name"               =>"delete",
			      "description"        =>"delete folder",# N_("delete folder")
			      "group_default"       =>"N"),
			array(
			      "name"               =>"open",
			      "description"        =>"open folder",# N_("open folder")
			      "group_default"       =>"N"),
			array(
			      "name"               =>"modify",
			      "description"        =>"modify folder",# N_("modify folder")
			      "group_default"       =>"N")
			);
  var $defDoctype='P';
  var $defProfFamId=FAM_ACCESSDIR;

  function PDir($dbaccess='', $id='',$res='',$dbid=0) {
    // don't use Doc constructor because it could call this constructor => infinitive loop
    DbObjCtrl::DbObjCtrl($dbaccess, $id, $res, $dbid);
  }



}

?>