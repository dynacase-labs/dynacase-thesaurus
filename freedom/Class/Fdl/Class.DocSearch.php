<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Class.DocSearch.php,v 1.19 2004/06/25 12:50:27 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: Class.DocSearch.php,v 1.19 2004/06/25 12:50:27 eric Exp $
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

$CLASS_CONTACT_PHP = '$Id: Class.DocSearch.php,v 1.19 2004/06/25 12:50:27 eric Exp $';


include_once("FDL/Class.PDocSearch.php");
include_once("FDL/Lib.Dir.php");


Class DocSearch extends PDocSearch {
  

  var $defDoctype='S';
  var $defaultedit= "FREEDOM:EDITSEARCH";


  function DocSearch($dbaccess='', $id='',$res='',$dbid=0) {

    PDocSearch::PDocSearch($dbaccess, $id, $res, $dbid);
    if (((! isset($this->fromid))) || ($this->fromid == "")) $this->fromid = FAM_SEARCH;
  }

  function AddQuery($tquery) {
    //print "AddQuery($query)";
    // insert query in search document
    if (is_array($tquery)) $query=implode(";\n",$tquery);
    else $query=$tquery;


    $oqd = new QueryDir($this->dbaccess);
    $oqd->dirid = $this->id;
    $oqd->qtype="M"; // multiple
    $oqd->query = $query;

    $this->exec_query("delete from fld where dirid=".$this->id." and qtype='M'");
    $err= $oqd-> Add();
    if ($err == "") {
      $this->setValue("SE_SQLSELECT",$query);
      $err=$this->modify();
    }
    return $err;
    
  }

  function GetQueryOld() {
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

  function getQuery() {
    if (! $this->isStaticSql()) {
    $query= $this->ComputeQuery($this->getValue("se_key"),
				 $this->getValue("se_famid"),
				 $this->getValue("se_latest"),
				 $this->getValue("se_case")=="yes",
				 $this->getValue("se_idfld"),
				 $this->getValue("se_sublevel") === "") ;
    // print "<HR>getQuery1:[$query]";
    } else {
      $query[]=$this->getValue("SE_SQLSELECT");
      // print "<BR><HR>".$this->getValue("se_latest")."/".$this->getValue("se_case")."/".$this->getValue("se_key");
      //  print "getQuery2:[$query]";
    }

    return $query;
  }

  function ComputeQuery($keyword="",$famid=-1,$latest="yes",$sensitive=false,$dirid=-1, $subfolder=true) {
    
    if ($dirid > 0) {
      if ($subfolder)  $cdirid = getRChildDirId($this->dbaccess, $dirid);
      else $cdirid=$dirid;
      
       
    } else $cdirid=0;

    $filters=array();

    if ($latest == "fixed") {
      $filters[] = "locked = -1";
      $filters[] = "lmodify = 'L'";       
    }
    $filters[] = "usefor = 'N'";
    $keyword= pg_escape_string($keyword);
    $keyword= str_replace("^","£",$keyword);
    $keyword= str_replace("$","\0",$keyword);
    if (strtolower(substr($keyword,0,5))=="::get") { // only get method allowed
	// it's method call
	$keyword = $this->ApplyMethod($keyword);
      }

    if ($keyword != "") {
      if ($sensitive) $filters[] = "values ~ '$keyword' ";
      else $filters[] = "values ~* '$keyword' ";
    }
 
  

    $query = getSqlSearchDoc($this->dbaccess, $cdirid, $famid, $filters,false,$latest=="yes");
    return $query;
  }


  /**
   * return true if the sqlselect is writted by hand
   * @return bool
   */
  function isStaticSql() {
    return (($this->getValue("se_latest") == "") && ($this->getValue("se_case")=="")&& ($this->getValue("se_key")==""));
  }

  function SpecRefresh() {
    if (! $this->isStaticSql()) {
      $query=$this->getQuery();

      $this->AddQuery($query);
    }
  }
  function editsearch() {

    
  global $action;

  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/edittable.js");
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FREEDOM/Layout/editdsearch.js");

  $tclassdoc=GetClassesDoc($this->dbaccess, $action->user->id);

  $this->lay->set("selfam",_("no family"));
  while (list($k,$cdoc)= each ($tclassdoc)) {
    $selectclass[$k]["idcdoc"]=$cdoc->initid;
    $selectclass[$k]["classname"]=$cdoc->title;
    if ($cdoc->initid == $famid) {
      $selectclass[$k]["selected"]="selected";
      $this->lay->set("selfam",$cdoc->title);
    } else $selectclass[$k]["selected"]="";
  }
  
  $this->lay->SetBlockData("SELECTCLASS", $selectclass);

    $this->editattr();
  }
}

?>