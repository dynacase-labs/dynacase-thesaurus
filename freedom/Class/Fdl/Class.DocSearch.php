<?php
// ---------------------------------------------------------------
// $Id: Class.DocSearch.php,v 1.7 2002/12/04 17:13:37 eric Exp $
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

$CLASS_CONTACT_PHP = '$Id: Class.DocSearch.php,v 1.7 2002/12/04 17:13:37 eric Exp $';


include_once("FDL/Class.PDocSearch.php");
include_once("FDL/Lib.Dir.php");


Class DocSearch extends PDocSearch {
  

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

    $this->exec_query("delete from fld where dirid=".$this->id." and qtype='M'");

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

  function ComputeQuery($keyword="",$famid=-1,$latest=false,$sensitive=false,$dirid=-1) {
    
    if ($dirid > 0) {

      $cdirid = getRChildDirId($this->dbaccess, $dirid);
      $cdirid[] = $dirid;
       
    } else $cdirid=0;;

    $filters=array();
    if ($latest)       $filters[] = "locked != -1";
    $filters[] = "usefor = 'N'";
    $keyword= str_replace("^","£",$keyword);
    $keyword= str_replace("$","£",$keyword);
    if ($keyword != "") {
      if ($sensitive) $filters[] = "values ~ '$keyword' ";
      else $filters[] = "values ~* '$keyword' ";
    }
 
  

    $query = getSqlSearchDoc($this->dbaccess, $cdirid, $famid, $filters);
    return $query;
  }




  function SpecRefresh() {
    if ($this->getValue("se_latest") != "") {
      $query=$this->ComputeQuery($this->getValue("se_key"),
				 $this->getValue("se_famid"),
				 $this->getValue("se_latest")==_("yes"),
				 $this->getValue("se_case")==_("yes"),
				 $this->getValue("se_idfld"));

      $this->AddQuery($query);
    }
  }
}

?>