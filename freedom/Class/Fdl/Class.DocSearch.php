<?php
/**
 * Document searches classes
 *
 * @author Anakeen 2000 
 * @version $Id: Class.DocSearch.php,v 1.35 2005/11/18 13:24:13 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.PDocSearch.php");
include_once("FDL/Lib.Dir.php");


Class DocSearch extends PDocSearch {
  

  public $defDoctype='S';
  public $defaultedit= "FREEDOM:EDITSEARCH";


  function DocSearch($dbaccess='', $id='',$res='',$dbid=0) {

    PDocSearch::__construct($dbaccess, $id, $res, $dbid);
    if (((! isset($this->fromid))) || ($this->fromid == "")) $this->fromid = FAM_SEARCH;
  }

  function AddQuery($tquery) {
    // insert query in search document
    if (is_array($tquery)) $query=implode(";\n",$tquery);
    else $query=$tquery;

    if ($query == "") return "";

    if (substr($query,0,6) != "select") {
      AddWarningMsg(sprintf(_("query [%s] not valid for select document"), $query));
      return sprintf(_("query [%s] not valid for select document"), $query);
    }
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

  /**
   * return true if the search has parameters
   */
  function isParameterizable() {
    return false;
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

  /**
   * return SQL query(ies) needed to search documents
   * @return array string
   */
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


  function getSqlGeneralFilters($keyword,$latest,$sensitive) {
    $filters=array();

    if ($latest == "fixed") {
      $filters[] = "locked = -1";
      $filters[] = "lmodify = 'L'";       
    } else if ($latest == "allfixed") {
      $filters[] = "locked = -1";
    } 
    $op= ($sensitive)?'~':'~*';
    //    $filters[] = "usefor != 'D'";
    $keyword= pg_escape_string($keyword);
    $keyword= str_replace("^","£",$keyword);
    $keyword= str_replace("$","\0",$keyword);
    if (strtolower(substr($keyword,0,5))=="::get") { // only get method allowed
      // it's method call
      $keyword = $this->ApplyMethod($keyword);
      $filters[] = "values $op '$keyword' ";
    } else if ($keyword != "") {
      // transform conjonction
      $tkey=explode(" ",$keyword);
	  $ing=false;
      foreach ($tkey as $k=>$v) {
	if ($ing) {
	  if ($v[strlen($v)-1]=='"') {
	    $ing=false;
	    $ckey.=" ".substr($v,0,-1);
	    $filters[] = "values $op '$ckey' ";	    
	  } else {
	    $ckey.=" ".$v;
	  }
	} else if ($v[0]=='"') {
	  if ($v[strlen($v)-1]=='"') {	    
	    $ckey=substr($v,1,-1);
	    $filters[] = "values $op '$ckey' ";	  
	  } else {
	    $ing=true;
	    $ckey=substr($v,1);
	  }
	} else {
	  $filters[] = "values $op '$v' ";	  
	}
      }
    }


   
    return $filters;
  }

  function ComputeQuery($keyword="",$famid=-1,$latest="yes",$sensitive=false,$dirid=-1, $subfolder=true) {
    if ($dirid > 0) {
      if ($subfolder)  $cdirid = getRChildDirId($this->dbaccess, $dirid);
      else $cdirid=$dirid;
      
       
    } else $cdirid=0;

    
    $filters=$this->getSqlGeneralFilters($keyword,$latest,$sensitive);

    $query = getSqlSearchDoc($this->dbaccess, $cdirid, $famid, $filters,false,$latest=="yes",$this->getValue("se_trash"));
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
    $err="";

    if (! $this->isStaticSql()) {
      if (! $this->isParameterizable()) $query=$this->getQuery();
      else $query='select id from doc where false';
      $err=$this->AddQuery($query);
    }
    return $err;
  }
  function editsearch() {    
    global $action;

    $rtarget=getHttpVars("rtarget");
    $this->lay->set("rtarget",$rtarget);
    $this->lay->set("restrict",false);
    $dirid = GetHttpVars("dirid"); // to set restriction family
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/edittable.js");
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FREEDOM/Layout/editdsearch.js");
    $famid=$this->getValue("se_famid");
    $classid=0;
    if ($dirid > 0) {
      $dir = new_Doc($this->dbaccess, $dirid);
      if (method_exists($dir,"isAuthorized")) {	
	if ($dir->isAuthorized($classid)) { 
	  // verify if classid is possible
	  if ($dir->norestrict) $tclassdoc=GetClassesDoc($this->dbaccess, $action->user->id,$classid,"TABLE");
	  else {
	    $tclassdoc=$dir->getAuthorizedFamilies();
	    $this->lay->set("restrict",true);
	  }
	} else  {
	  $tclassdoc=$dir->getAuthorizedFamilies();
	  $first = current($tclassdoc);
	  $famid = $first["id"];
	  $this->lay->set("restrict",true);
	}
      }
      else {
	$tclassdoc = GetClassesDoc($this->dbaccess, $action->user->id,$classid,"TABLE");
      }
    } else {
      $tclassdoc = GetClassesDoc($this->dbaccess, $action->user->id,$classid,"TABLE");
    }

    $this->lay->set("selfam",_("no family"));
    while (list($k,$cdoc)= each ($tclassdoc)) {
      $selectclass[$k]["idcdoc"]=$cdoc["id"];
      $selectclass[$k]["classname"]=$cdoc["title"];
      if ($cdoc["initid"] == $famid) {
	$selectclass[$k]["selected"]="selected";
	$this->lay->set("selfam",$cdoc["title"]);
      } else $selectclass[$k]["selected"]="";
    }
  
    $this->lay->SetBlockData("SELECTCLASS", $selectclass);

    $this->editattr();
  }

  function editspeedsearch() {
    return $this->editsearch();
  }

  /**
   * return document includes in search folder
   * @param bool $controlview if false all document are returned else only visible for current user  document are return
   * @param array $filter to add list sql filter for selected document
   * @param int $famid family identificator to restrict search 
   * @return array array of document array
   */
  function getContent($controlview=true,$filter=array(),$famid="") {
    if ($controlview) $uid=$this->userid;
    else $uid=1;
    $tdoc = getChildDoc($this->dbaccess, $this->initid ,0,"ALL", $filter, $uid, "TABLE",$famid);
    return $tdoc;
    
  }
}

?>