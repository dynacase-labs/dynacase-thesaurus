<?php
// ---------------------------------------------------------------
// $Id: Class.DocFile.php,v 1.2 2001/12/13 17:45:01 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Attic/Class.DocFile.php,v $
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
// $Log: Class.DocFile.php,v $
// Revision 1.2  2001/12/13 17:45:01  eric
// ajout attribut classname sur les doc
//
// Revision 1.1  2001/11/21 08:40:34  eric
// ajout historique
//
// Revision 1.2  2001/11/09 18:54:21  eric
// et un de plus
// ---------------------------------------------------------------
$CLASS_CONTACT_PHP = '$Id: Class.DocFile.php,v 1.2 2001/12/13 17:45:01 eric Exp $';


include_once("FREEDOM/Class.Doc.php");




Class DocFile extends Doc
{
    // --------------------------------------------------------------------
  //---------------------- OBJECT CONTROL PERMISSION --------------------
    // --------------------------------------------------------------------
  //---------------------- OBJECT CONTROL PERMISSION --------------------
  
  var $obj_acl = array (
			array(
			      "name"		=>"view",
			      "description"	=>"view document", // N_("view document")
			      "group_default"       =>"Y"),
			array(
			      "name"               =>"edit",
			      "description"        =>"edit document"),// N_("edit document")
			array(
			      "name"               =>"delete",
			      "description"        =>"delete document",// N_("delete document")
			      "group_default"       =>"N")
			);

  // ------------
  var $defDoctype='F';
  var $defClassname='DocFile';

  function DocFile($dbaccess='', $id='',$res='',$dbid=0) {
     DbObjCtrl::DbObjCtrl($dbaccess, $id, $res, $dbid);
  }
}

?>