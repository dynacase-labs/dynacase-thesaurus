<?php
// ---------------------------------------------------------------
// $Id: Lib.Dir.php,v 1.28 2002/10/22 13:35:08 eric Exp $
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
    
    
    $docfld = new Dir($dbaccess);
  $condfld="  hasviewprivilege($userid,doc.profid) ";
  $conddoctype="doc.doctype='".$docfld->defDoctype."' ";
  
  if (! $notfldsearch) {
    // include conditions for get search document
      
      $docse = new DocSearch($dbaccess);
    $conddoctype="(doc.doctype='".$docfld->defDoctype."' or doc.doctype='".$docse->defDoctype."')";
    
    
  }
  
  $condfld = "($conddoctype) and ($condfld) ";
  
  
  $qsql =  "select doc.* from fld, doc where fld.dirid=$dirid ".
    "and doc.id=fld.childid and ($condfld) and not doc.useforprof ".
      "order by doc.title";
  
  $query = new QueryDb($dbaccess,"Doc");
  $tableq=$query->Query(0,0,$restype,$qsql);
  
  if ($query->nb == 0) return array();            
  
  return($tableq);
}



function getChildDoc($dbaccess, 
		     $dirid, 
		     $start="0", $slice="ALL", $sqlfilters=array(), 
		     $userid=1, 
		     $qtype="LIST", $wvalue=false) {
  
  // query to find child documents
    
    
  if (count($sqlfilters)>0)    $sqlcond = "and (".implode(") and (", $sqlfilters).")";
  else $sqlcond = "";
  if ($userid > 1) $sqlcondpriv = " and hasviewprivilege($userid,doc.profid) ";
  else $sqlcondpriv ="";
  
  if ($wvalue) $sqlwvalue=", getdocvalues(doc.id) as sqlvalues ";
  else $sqlwvalue="";
  
  
  if ($dirid == 0) {
    // search in all Db
    $qsql= "select doc.* $sqlwvalue".
       "from doc  ".
       "where (doc.doctype != 'T')  ".
       $sqlcond.
       $sqlcondpriv.
       " order by title LIMIT $slice OFFSET $start;";
  } else {
    $fld = new Dir($dbaccess, $dirid);
    
    if ( $fld->classname != 'DocSearch') {
      
      $qsql= "select distinct doc.* $sqlwvalue".
	 "from doc   ".
	 "where (doc.doctype != 'T')  ".
	 $sqlcond.
	 "and ((doc.initid in (select childid from fld where (qtype='S') and (dirid=$dirid)) and doc.locked != -1)".
	 "   or (doc.id in (select childid from fld where (qtype='F') and (dirid=$dirid))))".
	 $sqlcondpriv.
	 " order by doc.title LIMIT $slice OFFSET $start;";
      
      $qsql= "select  doc.* $sqlwvalue".
             "from doc, fld  ".
	     "where   (doc.doctype != 'T') $sqlcond ".
	     "and fld.dirid=$dirid ".
	     "and ((fld.qtype='S' and fld.childid=doc.initid and doc.locked != -1) ) ".
	     //	     " or (fld.qtype='F' and fld.childid=doc.id)) ".
		  
	     $sqlcondpriv.
	     " order by doc.title LIMIT $slice OFFSET $start;";
      
      
    } else {
      // search familly
      $docsearch = new QueryDb($dbaccess,"QueryDir");
      $docsearch ->AddQuery("dirid=$dirid");
      $docsearch ->AddQuery("qtype!='S'");
      $ldocsearch = $docsearch ->Query();
      
      
      
      // for the moment only one query search
      if (($docsearch ->nb) > 0) {
	switch ($ldocsearch[0]->qtype) {
	  // 	  case "C":  // just condition
	  // 	  $qsql= "select * ".
	  // 	    "from doc  ".
	  // 	      "where (doc.doctype != 'T')  ".
	  // 		"and ({$ldocsearch[0]->query}) ".
	  // 		    $sqlcond.
	  // 		      " order by title LIMIT $slice OFFSET $start;";
	  //	  break;
	case "M": // complex query
	    
	    
	  $qsql= "{$ldocsearch[0]->query} ".
	     $sqlcond.$sqlcondpriv.
	     " order by doc.title LIMIT $slice OFFSET $start;";
	$qsql= str_replace(array("select *","select doc.*"), "select distinct doc.* $sqlwvalue ", $qsql);
	break;
	}
      } else {
	return array(); // no query avalaible
      }
    }
  }
  
  // print "<HR>".$qsql;
  $query = new QueryDb($dbaccess,"Doc");
  
  
  $tableq=$query->Query(0,0,$qtype,$qsql);
  
  if ($query->nb == 0)
    {
      return array();
    }
  
  // add values in comprehensive structure
  if ($wvalue) {
    if ($qtype=="TABLE") {
      while(list($k,$v) = each($tableq)) {
	$tableq[$k] += sqlval2array($v["sqlvalues"]);
      } 
    } else {
      while(list($k,$v) = each($tableq)) {
	$tableq[$k]->values= sqlval2array($v->sqlvalues);
      } 
    }
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

//same getChildDoc with value : return array , not doc object
function getChildDocValue($dbaccess, $dirid, $start="0", $slice="ALL", $sqlfilters=array(), $userid=1) {
  
  
  return getChildDoc($dbaccess, $dirid, $start, $slice, $sqlfilters, $userid, "TABLE", true);
  
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
  
  $count = $query->Query(0,0,"TABLE", "select count(*) from fld, doc where fld.dirid=$dirid and doc.id=fld.childid and (doc.classname='Dir' or doc.classname='DocSearch') and not doc.useforprof");
  return (($query->nb > 0) && ($count[0]["count"] > 0));
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
  if (isset($v[$k])) return $v[$k];
  return $d;
}


// --------------------------------------------------------------------
function GetClassesDoc($dbaccess,$userid,$classid=1)
     // --------------------------------------------------------------------
{
  $query = new QueryDb($dbaccess,"Doc");
  
  
  $query->AddQuery("doctype='C'");
  
  $cdoc = new Doc($dbaccess, $classid);
  if ($cdoc->useforprof == "t") $query->AddQuery("(useforprof)");
  else {
    $query->AddQuery("(not useforprof)");
    switch ($classid) {
    case FAM_ACCESSDOC:
    case FAM_ACCESSDIR:
    case FAM_ACCESSSEARCH:
      $query->AddQuery("(useforprof)");
      break;
    case FAM_SEARCH:
      $query->AddQuery("(id = ".FAM_SEARCH.")");
      break;
    case FAM_DIR:
      $query->AddQuery("(id = ".FAM_DIR.")");
      break;
    default:	
      $query->AddQuery("(id = 1) OR (id > 5)");
    }
    //      $query->AddQuery("initid=id");
  }
  
  $query->AddQuery("hasviewprivilege(".$userid.",doc.profid)");
  $query->order_by="doc.title";
  return $query->Query();
}



?>
