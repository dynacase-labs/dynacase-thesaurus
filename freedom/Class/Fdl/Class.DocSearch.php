<?php
// ---------------------------------------------------------------
// $Id: Class.DocSearch.php,v 1.6 2002/10/31 08:09:23 eric Exp $
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

$CLASS_CONTACT_PHP = '$Id: Class.DocSearch.php,v 1.6 2002/10/31 08:09:23 eric Exp $';


include_once("FDL/Class.PDocSearch.php");




Class DocSearch extends PDocSearch
{
    // --------------------------------------------------------------------
  //---------------------- OBJECT CONTROL PERMISSION --------------------
  

  var $defDoctype='S';


  function DocSearch($dbaccess='', $id='',$res='',$dbid=0) {

     PDocSearch::PDocSearch($dbaccess, $id, $res, $dbid);
     if (((! isset($this->fromid))) || ($this->fromid == "")) $this->fromid = FAM_SEARCH;
  }

  function AddQuery($query) {
    
    // insert query in search document
    $oqd = new QueryDir($this->dbaccess);
    $oqd->dirid = $this->id;
    $oqd->qtype="M"; // multiple
    $oqd->query = $query;
    return $oqd-> Add();
    
  }

  function GetQuery() {
    $query = new QueryDb($this->dbaccess, "QueryDir");
    $query->AddQuery("dirid=".$this->id);
    $query->AddQuery("qtype != 'S'");
    $tq=$query->Query(0,0,"TABLE");


    if ($query->nb > 0)
	{
	  return $tq[0]["query"];
	}
    return "";
  }
}

?>