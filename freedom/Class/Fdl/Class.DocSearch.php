<?php
// ---------------------------------------------------------------
// $Id: Class.DocSearch.php,v 1.1 2002/02/13 14:31:58 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Class.DocSearch.php,v $
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
// $Log: Class.DocSearch.php,v $
// Revision 1.1  2002/02/13 14:31:58  eric
// ajout usercard application
//
// Revision 1.3  2002/02/05 16:34:07  eric
// decoupage pour FREEDOM-LIB
//
// Revision 1.2  2002/01/04 15:45:54  eric
// correction pour init DocSerach
//
// Revision 1.1  2001/12/21 13:59:45  eric
// modif pour incident
//
// Revision 1.1  2001/11/21 08:40:34  eric
// ajout historique
//
// Revision 1.2  2001/11/09 18:54:21  eric
// et un de plus
// ---------------------------------------------------------------
$CLASS_CONTACT_PHP = '$Id: Class.DocSearch.php,v 1.1 2002/02/13 14:31:58 eric Exp $';


include_once("FDL/Class.Doc.php");




Class DocSearch extends Doc
{
    // --------------------------------------------------------------------
  //---------------------- OBJECT CONTROL PERMISSION --------------------
  
  var $obj_acl = array (
			array(
			      "name"		=>"view",
			      "description"	=>"view search", // N_("view directory")
			      "group_default"       =>"Y"),
			array(
			      "name"               =>"edit",
			      "description"        =>"edit search"),// N_("edit directory")
			array(
			      "name"               =>"delete",
			      "description"        =>"delete search",// N_("delete directory")
			      "group_default"       =>"N"),
			array(
			      "name"               =>"execute",
			      "description"        =>"execute search",// N_("open directory")
			      "group_default"       =>"N")
			);
  var $defDoctype='S';
  var $defClassname='DocSearch';

  function DocSearch($dbaccess='', $id='',$res='',$dbid=0) {

     DbObjCtrl::DbObjCtrl($dbaccess, $id, $res, $dbid);
     if (((! isset($this->fromid))) || ($this->fromid == "")) $this->fromid = FAM_SEARCH;
  }

  function AddQuery($query) {
    
    // insert query in search document
    $oqd = new QueryDir($this->dbaccess);
    $oqd->dirid = $this->id;
    $oqd->qtype="M"; // multiple
    $oqd->query = $query;
    $oqd-> Add();
    
  }
}

?>