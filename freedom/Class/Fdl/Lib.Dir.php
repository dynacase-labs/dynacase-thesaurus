<?php
// ---------------------------------------------------------------
// $Id: Lib.Dir.php,v 1.48 2002/12/16 17:47:37 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Lib.Dir.php,v $
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
include_once('FDL/Class.Dir.php');
include_once('FDL/Class.DocSearch.php');
include_once('FDL/Class.DocFam.php');

function getFirstDir($dbaccess) {
  // query to find first directories
    $qsql= "select id from doc  where  (doctype='D') order by id LIMIT 1;";
  
  
  
  $query = new QueryDb($dbaccess,"Doc");
  
  $tableq=$query->Query(0,0,"TABLE",$qsql);
  if ($query->nb > 0)
    {
      
      return $tableq[0]["id"];
    }
  
  
  return(0);
}


function getChildDir($dbaccess, $userid, $dirid, $notfldsearch=false, $restype="LIST") {
  // query to find child directories (no recursive - only in the specified folder)
    
    
    if (!($dirid > 0)) return array();   
  
  // search classid and appid to test privilege
    if ($notfldsearch) {
      // just folder no serach
      return  getChildDoc($dbaccess,$dirid,"0","ALL",array(),$userid,$restype,2);
    } else {
      // with folder and searches
      
      return  array_merge(getChildDoc($dbaccess,$dirid,"0","ALL",array("doctype='D'"),$userid,$restype,2),
			  getChildDoc($dbaccess,$dirid,"0","ALL",array("doctype='S'"),$userid,$restype,5));
      
    }
      
}


function getSqlSearchDoc($dbaccess, 
			 $dirid,  // in a specific folder (0 => in all DB)
			 $fromid, // for a specific familly (0 => all familly) (<0 strict familly)
			 $sqlfilters=array(),
			 $distinct=false,// if want distinct without locked
			 $latest=true) {// only latest document

 
  

  $table="doc";$only="";
  if ($fromid != 0) $table="doc$fromid";
  if ($fromid < 0) $only="only" ;

  if ($distinct) {
    $selectfields =  "distinct on (initid) $table.*";
  } else {
    $selectfields =  "$table.*"; 
    $sqlfilters[-2] = "doctype != 'T'";
    ksort($sqlfilters);

  }

  $sqlcond="";
  if (count($sqlfilters)>0)    $sqlcond = " (".implode(") and (", $sqlfilters).")";



  if ($dirid == 0) {
    //-------------------------------------------
    // search in all Db
    //-------------------------------------------
    
    if ($latest) $sqlfilters[-1] = "locked != -1";
    if (count($sqlfilters)>0)    $sqlcond = " (".implode(") and (", $sqlfilters).")";
    

    $qsql= "select $selectfields ".
      "from $only $table  ".
      "where  ".
      $sqlcond;
  } else {

    //-------------------------------------------
    // in a specific folder
    //-------------------------------------------

    
    if (! is_array($dirid))    $fld = new Doc($dbaccess, $dirid);

    if ((is_array($dirid)) || ( $fld->defDoctype != 'S'))  {


    
    if ($latest) $sqlfilters[-1] = "locked != -1";
    if (count($sqlfilters)>0)    $sqlcond = " (".implode(") and (", $sqlfilters).")";
      
      if (is_array($dirid)) {
	$sqlfld=GetSqlCond($dirid,"dirid",true);
      } else {
	$sqlfld = "fld.dirid=$dirid and qtype='S'";
      }
      


//        $qsql_Litle= "select $selectfields ".
//  	"from  $table  ".
//  	"where initid in (select childid from fld where $sqlfld)   $sqlcond ";

      $qsql= "select $selectfields ".
	"from (select childid from fld where $sqlfld) as fld2 left outer join $table on (initid=childid)  ".
	"where  $sqlcond ";

//        $qsql_Medium= "select $selectfields ".
//  	"from (select childid from fld where $sqlfld) as fld2 inner join $table on (initid=childid)  ".
//  	"where  $sqlcond ";


    } else {
      //-------------------------------------------
      // search familly
      //-------------------------------------------
      $docsearch = new QueryDb($dbaccess,"QueryDir");
      $docsearch ->AddQuery("dirid=$dirid");
      $docsearch ->AddQuery("qtype != 'S'");
      $ldocsearch = $docsearch ->Query(0,0,"TABLE");
      
      
      
      // for the moment only one query search
      if (($docsearch ->nb) > 0) {
	switch ($ldocsearch[0]["qtype"]) {
	 
	case "M": // complex query
	    
	  $sqlM=$ldocsearch[0]["query"];

	 
	  if ($fromid > 0) $sqlM=str_replace("from doc ","from $only $table ",$sqlM);
	    
	  $qsql= $sqlM ." and " . $sqlcond;
	
	  break;
	}
      } else {
	return false; // no query avalaible
      }
    }

  }
  return $qsql;
}

function getChildDoc($dbaccess, 
		     $dirid, 
		     $start="0", $slice="ALL", $sqlfilters=array(), 
		     $userid=1, 
		     $qtype="LIST", $fromid="",$distinct=false) {
  
  // query to find child documents            
  
  $qsql=getSqlSearchDoc($dbaccess,$dirid,$fromid,$sqlfilters,$distinct);

  if ($userid > 1) { // control view privilege
     $qsql .= " and (profid <= 0 or hasviewprivilege($userid, profid))";
    // and get permission
    if ($qtype == "LIST") $qsql = str_replace("* from ","* ,getuperm($userid,profid) as uperm from ",$qsql);
  }


  if ($distinct) $qsql .= " ORDER BY initid, id desc  LIMIT $slice OFFSET $start;";
  else  $qsql .= " ORDER BY title LIMIT $slice OFFSET $start;";
   
   if ($fromid > 0) include_once "FDLGEN/Class.Doc$fromid.php";

   
  $query = new QueryDb($dbaccess,"Doc$fromid");
  
  $mb=microtime();

  $tableq=$query->Query(0,0,$qtype,$qsql);
 
  
  //  print "<HR>".$query->LastQuery; print " - $qtype<B>".microtime_diff(microtime(),$mb)."</B>";
  


  if ($query->nb == 0)
    {
      return array();
    }
  

  
  reset($tableq);
  
  return($tableq);
}


function sqlval2array($sqlvalue) {
  // return values in comprehensive structure
    
    $rt = array();
  if ($sqlvalue != "") {
    $vals = split("\]\[",substr($sqlvalue,1,-1));
    while(list($k1,$v1) = each($vals)) {
      list($aname,$aval)=split(";;",$v1);
      $rt[$aname]=$aval;
    }
    
  }
  return $rt;
}



function getChildDirId($dbaccess, $dirid, $notfldsearch=false) {
  // query to find child directories (no recursive - only in the specified folder)
    
    if ($notfldsearch) $odoctype='D';
    else $odoctype='S';
  $qsql= "select distinct on (doc.id) * from doc  ".
    "where  ((doc.doctype='D') OR (doc.doctype='$odoctype')) ".
      "and doc.initid in (select childid from fld where (qtype='S') and (dirid=$dirid)) ";
  
  
  $tableid = array();
  $query = new QueryDb($dbaccess,"Doc");
  $query -> AddQuery("dirid=".$dirid);
  
  
  $tableq=$query->Query(0,0,"LIST",$qsql);
  if ($query->nb == 0) return array();  
  
  reset($tableq);
  while(list($k,$v) = each($tableq)) {
    $tableid[] = $v->id;
  }
  
  
  return($tableid);
}


// --------------------------------------------------------------------
function getRChildDirId($dbaccess, $dirid, $rchilds=array(), $level=0) {
  // --------------------------------------------------------------------
  // query to find child directories (RECURSIVE)
  global $action;

  
  if ($level > 20) {
    $action->log->warning("Max dir deep [$level levels] reached");
    echo("<h3>Max dir deep [$level levels] reached</h3>");
    exit; // limit recursivity
  }

  $rchilds[] = $dirid;

  $childs = getChildDirId($dbaccess, $dirid, true);

  if (count($childs) > 0) {
    while(list($k,$v) = each($childs)) {
      if (!in_array($v,$rchilds)) {
	$t = array_merge($rchilds, getRChildDirId($dbaccess,$v,$rchilds,$level+1));
	if (is_array($t)) $rchilds = array_values(array_unique($t));
      }
    }
  } 
  return($rchilds);
}

function isInDir($dbaccess, $dirid, $docid) {
  // return true id docid is in dirid
    
    
    $query = new QueryDb($dbaccess,"QueryDir");
  $query -> AddQuery("dirid=".$dirid);
  $query -> AddQuery("childid=".$docid);
  
  $query->Query(0,0,"TABLE");
  return ($query->nb > 0);
}

function hasChildFld($dbaccess, $dirid) {
  // return true id dirid has one or more child dir
    
    
  $query = new QueryDb($dbaccess,"QueryDir");  
  $count = $query->Query(0,0,"TABLE", "select count(*) from fld, doc2 where fld.dirid=$dirid and childid=doc2.id");
  if (($query->nb > 0) && ($count[0]["count"] > 0)) return true;


  $count = $query->Query(0,0,"TABLE", "select count(*) from fld, doc5 where fld.dirid=$dirid and childid=doc5.id");
  if (($query->nb > 0) && ($count[0]["count"] > 0)) return true;

  return false;
}

// --------------------------------------------------------------------
function getQids($dbaccess, $dirid, $docid) {
  // return array of document id includes in a directory
    // --------------------------------------------------------------------
      
      $tableid = array();
  
  $doc = new Doc($dbaccess, $docid);
  $query = new QueryDb($dbaccess,"QueryDir");
  $query -> AddQuery("dirid=".$dirid);
  $query -> AddQuery("((childid=$docid) and (qtype='F')) OR ((childid={$doc->initid}) and (qtype='S'))");
  $tableq=$query->Query();
  
  if ($query->nb > 0)
    {
      while(list($k,$v) = each($tableq)) 
	{
	  $tableid[$k] = $v->id;
	}
      unset ($tableq);
    }
  
  
  return($tableid);
}

// just to test array if set before
function setv($v,$k,$d="") {
  if (isset($v[$k]) && ($v[$k] != "")) return $v[$k];
  return $d;
}


// --------------------------------------------------------------------
function GetClassesDoc($dbaccess,$userid,$classid=1)
     // --------------------------------------------------------------------
{
  $query = new QueryDb($dbaccess,"DocFam");
  
  
  $query->AddQuery("doctype='C'");
  
  $cdoc = new DocFam($dbaccess, $classid);
  if ($cdoc->usefor == "P") $query->AddQuery("usefor = 'P'");
  else {

    switch ($classid) {
    case FAM_ACCESSDOC:
    case FAM_ACCESSDIR:
    case FAM_ACCESSSEARCH:
      
      
      break;
    case FAM_SEARCH:
      $query->AddQuery("(id = ".FAM_SEARCH.")");
      break;
    case FAM_DIR:
      $query->AddQuery("(id = ".FAM_DIR.")");
      break;
    default:	
      $query->AddQuery("usefor != 'P'");
    }
    //      $query->AddQuery("initid=id");
  }
  
  $query->AddQuery("hasviewprivilege(".$userid.",docfam.profid)");
  $query->order_by="title";
  return $query->Query();
}



?>
