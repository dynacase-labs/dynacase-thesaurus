<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Lib.Dir.php,v 1.98 2004/12/28 17:02:37 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once('FDL/Class.Dir.php');
include_once('FDL/Class.DocSearch.php');
include_once('FDL/Class.DocFam.php');

function getFirstDir($dbaccess) {
  // query to find first directories
    $qsql= "select id from only doc2  where  (doctype='D') order by id LIMIT 1;";
  
  
  
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
  if ($fromid == -1) $table="docfam";
  elseif ($fromid < 0) {$only="only" ;$fromid=-$fromid;}
  elseif ($fromid != 0) $table="doc$fromid";

  if ($distinct) {
    $selectfields =  "distinct on (initid) $table.*";
  } else {
    $selectfields =  "$table.*"; 
    $sqlfilters[-2] = "doctype != 'T'";
    ksort($sqlfilters);

  }

  $sqlcond="";
  ksort($sqlfilters);
  if (count($sqlfilters)>0)    $sqlcond = " (".implode(") and (", $sqlfilters).")";



  if ($dirid == 0) {
    //-------------------------------------------
    // search in all Db
    //-------------------------------------------
    
    $sqlfilters[-3] = "doctype != 'Z'";
    if ($latest) $sqlfilters[-1] = "locked != -1";
    ksort($sqlfilters);
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


      $sqlfilters[-3] = "doctype != 'Z'";
    
      if ($latest) $sqlfilters[-1] = "locked != -1";
      ksort($sqlfilters);
      if (count($sqlfilters)>0)    $sqlcond = " (".implode(") and (", $sqlfilters).")";
      
      if (is_array($dirid)) {
	$sqlfld=GetSqlCond($dirid,"dirid",true);
      } else {
	$sqlfld = "fld.dirid=$dirid and qtype='S'";
      }
      


     //            $qsql= "select $selectfields ".
//           	"from  $table  ".
//           	"where initid in (select childid from fld where $sqlfld) and   $sqlcond ";

//                 $qsql= "select $selectfields ".
//           	"from  fld,$table  ".
//           	"where initid = childid and ($sqlfld) and   $sqlcond ";

      //       $qsql= "select $selectfields ".
      // 	"from (select childid from fld where $sqlfld) as fld2 left outer join $table on (initid=childid)  ".
      // 	"where  $sqlcond ";

      //  if ($table != "doc") {
      $qsql= "select $selectfields ".
   	"from (select childid from fld where $sqlfld) as fld2 inner join $table on (initid=childid)  ".
   	"where  $sqlcond ";

      //     } else {
      //          $qsql= "select * ".
      //    	"from sfolder   ".
      //    	"where  dirid=$dirid and $sqlcond ";
      //       }

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
	    
	  // $sqlM=$ldocsearch[0]["query"];

	  $fld=new Doc($dbaccess,$dirid);
	  $tsqlM=$fld->getQuery();
	  foreach ($tsqlM as $sqlM) {
	    if ($sqlM != false) {
	      if (! ereg("doctype[ ]*=[ ]*'Z'",$sqlM,$reg)) {
		$sqlfilters[-3] = "doctype != 'Z'";	   
		ksort($sqlfilters);
		if (count($sqlfilters)>0)    $sqlcond = " (".implode(") and (", $sqlfilters).")";
	      }
	      if ($fromid > 0) $sqlM=str_replace("from doc ","from $only $table ",$sqlM);
	    
	      $qsql[]= $sqlM ." and " . $sqlcond;
	    }
	  }
	  break;
	}
      } else {
	return false; // no query avalaible
      }
    }

  }
  if (is_array($qsql)) return $qsql;
  return array($qsql);
}
/**
 * get possibles errors before request of getChildDoc
 * @param string $dbaccess database specification
 * @param array  $dirid the array of id or single id of folder where search document 
 * @return array error codes
 */
function getChildDocError($dbaccess, 
			 $dirid) { // in a specific folder (0 => in all DB)

  $terr=array();


  if ($dirid == 0) {
    //-------------------------------------------
    // search in all Db
    //-------------------------------------------
   
  } else {

    //-------------------------------------------
    // in a specific folder
    //-------------------------------------------

    if (! is_array($dirid))    $fld = new Doc($dbaccess, $dirid);
    if ((is_array($dirid)) || ( $fld->defDoctype != 'S'))  {


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
	    

	  $fld=new Doc($dbaccess,$dirid);
	  $tsqlM=$fld->getQuery();
	  foreach ($tsqlM as $sqlM) {

	    if ($sqlM == false) $terr[$dirid]=_("uncomplete request"); // uncomplete
	    
	  }
	  break;
	}
      } else {
	$terr[$dirid]=_("request not found"); // not found
      }
    }

  }
  return $terr;
}
/**
 * return array of documents
 *
 * @param string $dbaccess database specification
 * @param array  $dirid the array of id or single id of folder where search document 
 * @param string $start the start index 
 * @param string $slice the maximum number of returned document
 * @param array $sqlfilters array of sql filter
 * @param int $userid the current user id
 * @param string $qtype LIST|TABLE the kind of return : list of object or list or values array
 * @param int $fromid identificator of family document
 * @param bool $distinct if true all revision of the document are returned else only latest
 * @param string $orderby field order
 * @param bool $latest if true only latest else all revision
 * @return array/Doc
 */
function getChildDoc($dbaccess, 
		     $dirid, 
		     $start="0", $slice="ALL", $sqlfilters=array(), 
		     $userid=1, 
		     $qtype="LIST", $fromid="",$distinct=false, $orderby="title",$latest=true) {
  
  global $action;

  // query to find child documents          
  if (($fromid!="") && (! is_numeric($fromid))) $fromid=getFamIdFromName($dbaccess,$fromid);
  if ($fromid==0) $fromid="";
  if (($fromid=="") && ($dirid!=0)&&($qtype=="TABLE")) {

    $fld = new Doc($dbaccess, $dirid);

    // In case of full text search, execute specific code
    if ($fld->fromid == getFamIdFromName($dbaccess,"FTEXTSEARCH")) 
      return $fld->GetFullTextResultDocs($dbaccess, $dirid, $start, $slice, $sqlfilters, 
					 $userid, $qtype, $fromid, $distinct, $orderby, $latest);
    
    if ( $fld->defDoctype != 'S') {
      // try optimize containt of folder
      $td=getFldDoc($dbaccess,$dirid,$sqlfilters);
      if (is_array($td)) return $td;
    } 
  }
  $tqsql=getSqlSearchDoc($dbaccess,$dirid,$fromid,$sqlfilters,$distinct,$latest);

  $tretdocs=array();
  foreach ($tqsql as $qsql) {
    if ($qsql != false) {
      if ($userid > 1) { // control view privilege
	$qsql .= " and (profid <= 0 or hasviewprivilege($userid, profid))";
	// and get permission
	$qsql = str_replace("* from ","* ,getuperm($userid,profid) as uperm from ",$qsql);
      }


      if ($start == "") $start="0";
      if ($distinct) $qsql .= " ORDER BY initid, id desc  LIMIT $slice OFFSET $start;";
      else  {
	if ($fromid == "") $orderby="title";
	elseif (substr($qsql,0,12)  == "select doc.*") $orderby="title";
	if ($orderby=="") $qsql .= "  LIMIT $slice OFFSET $start;";
	else $qsql .= " ORDER BY $orderby LIMIT $slice OFFSET $start;";
      }
   

      if ($fromid != "") {
	if ($fromid == -1) {
	  include_once "FDL$GEN/Class.DocFam.php";
	  $fromid="Fam";
	} else {
	  $fromid=abs($fromid);
	  if ($fromid > 0) {
	    $GEN=getGen($dbaccess);
	    include_once "FDL$GEN/Class.Doc$fromid.php";
	  }
	}
      }

   
      $query = new QueryDb($dbaccess,"Doc$fromid");
  
      $mb=microtime();

      $tableq=$query->Query(0,0,$qtype,$qsql);
 
 
      if ($query->nb > 0)
	{
	  $tretdocs=array_merge($tretdocs,$tableq);
	}
      // print "<HR>".$query->LastQuery; print " - $qtype<B>".microtime_diff(microtime(),$mb)."</B>";

    } else {
      // error in query          
    }
  }


  
  reset($tretdocs);
  
  return($tretdocs);
}





/** 
 * optimization for getChildDoc
 */
function getFldDoc($dbaccess,$dirid,$sqlfilters=array()) {
 
  if (is_array($dirid)) {
    $sqlfld=GetSqlCond($dirid,"dirid",true);
  } else {
    $sqlfld = "fld.dirid=$dirid";
  }
  
  $mc=microtime();
  
  $q = new QueryDb($dbaccess,"QueryDir");
  $q->AddQuery($sqlfld);
  $q->AddQuery("qtype='S'");

  $tfld=$q->Query(0,0,"TABLE");

  if ($q->nb > 100) return false;
  $t=array();
  if ($q->nb > 0) {
    foreach ($tfld as $k=>$v) {   

      $t[$v["childid"]]=getLatestTDoc($dbaccess,$v["childid"],$sqlfilters);
      
      if ($t[$v["childid"]] == false) unset($t[$v["childid"]]);
      if (($t[$v["childid"]]["uperm"] & (1 << POS_VIEW)) == 0) { // control view
	unset($t[$v["childid"]]);
      }
    }
  }
  //  print "<HR>"; print " - getFldDoc $dirid<B>".microtime_diff(microtime(),$mc)."</B>";
  return $t;
}

/** 
 * optimization for getChildDoc in case of grouped searches
 * not used
 */
function getMSearchDoc($dbaccess,$dirid,
		       $start="0", $slice="ALL",$sqlfilters=array(), 
		       $userid=1, 
		       $qtype="LIST", $fromid="",$distinct=false, $orderby="title",$latest=true) {
 
  $sdoc= new Doc($dbaccess, $dirid);

  $tidsearch=$sdoc->getTValue("SEG_IDCOND");
  $tdoc=array();
  foreach ($tidsearch as $k=>$v) {
    $tdoc=array_merge(getChildDoc($dbaccess,$v,
				  $start, $slice,$sqlfilters, 
				  $userid, 
				  $qtype, $fromid,$distinct, $orderby,$latest),
		      $tdoc);
  }
  return $tdoc;
    
}







/**
 * return array of documents
 *
 * based on {@see getChilDoc()} it return document with enum attribute condition
 * return document which the $aid attribute has the value $kid 
 *
 * @param string $dbaccess database specification
 * @param string $famname internal name of family document
 * @param string $aid the attribute identificator
 * @param string $kid the key for enum value to search
 * @param string $name additionnal filter on the title
 * @param array $sqlfilters array of sql filter
 * @param int $limit max document returned
 * @param string $qtype LIST|TABLE the kind of return : list of object or list or values array
 * @param int $userid the current user id
 * @return array/Doc
 */
function getKindDoc($dbaccess, 
		    $famname,
		    $aid, 
		    $kid, 
		    $name="", // filter on title
		    $sqlfilter=array(), 
		    $limit=100,
		    $qtype="TABLE",
		    $userid=0) {

  global $action;

  if ($userid==0) $userid=$action->user->id;
  
  $famid= getFamIdFromName($dbaccess,$famname);
  $fdoc = new Doc($dbaccess, $famid);

  // searches for all fathers kind
  $a = $fdoc->getAttribute($aid);
  if ($a) {
    $tkids=array();;
    $enum = $a->getEnum();
    while (list($k, $v) = each($enum)) {
      if (in_array($kid,explode(".",$k))) {
	$tkids[] = substr($k,strrpos(".".$k,'.'));
      }
    }
 
    if ($a->type == "enum") {
      if ($a->repeat) {
	$sqlfilter[] = "in_textlist($aid,'".
	  implode("') or in_textlist($aid,'",$tkids)."')";
      } else {
	$sqlfilter[] = "$aid='".
	  implode("' or $aid='",$tkids)."'";    
      }
    }
  }

  if ($name != "")  $sqlfilter[]="title ~* '$name'";

  return getChildDoc($dbaccess, 
		     0,0,$limit,$sqlfilter ,$userid,"TABLE",
		     getFamIdFromName($dbaccess,$famname));
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



function getChildDirId($dbaccess, $dirid) {
  // query to find child directories (no recursive - only in the specified folder)
        

  $tableid = array();
  
  $tdir=getChildDoc($dbaccess,$dirid,"0","ALL",array(),$userid,"TABLE",2);

  while(list($k,$v) = each($tdir)) {
    $tableid[] = $v["id"];
  }
  
  
  return($tableid);
}
// --------------------------------------------------------------------

/**
 * return array of subfolder id until sublevel 2 (RECURSIVE)
 *
 * @param string $dbaccess database specification
 * @param int  $dirid the id of folder where search subfolders 
 * @param array $rchilds use for recursion (dont't set anything)
 * @param int  $level use for recursion (dont't set anything)
 * @return array/int
 * @see getChildDir()
 */
function getRChildDirId($dbaccess, $dirid, $rchilds=array(), $level=0) { 
  global $action;

  
  if ($level > 2) {
    // $action->addWarningMsg("getRChildDirId::Max dir deep [$level levels] reached");
    return ($rchilds);
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

function hasChildFld($dbaccess, $dirid,$issearch=false) {
  // return true id dirid has one or more child dir
    
  if ($issearch) {
    $query = new QueryDb($dbaccess,"QueryDir");  
    $query->AddQuery("qtype='M'");
    $query->AddQuery("dirid=$dirid");
    $list=$query->Query(0,1,"TABLE");
    if ($list) {
      $oquery=$list[0]["query"];
      if (ereg("select (.+) from (.+)",$oquery,$reg)) {
	if (ereg("doctype",$reg[2],$treg)) return false; // do not test if special doctype searches
	$nq=sprintf("select count(%s) from %s and ((doctype='D')or(doctype='S')) limit 1",$reg[1],$reg[2]);
	$count=$query->Query(0,0,"TABLE",$nq);
	if (($query->nb > 0) && ($count[0]["count"] > 0)) return true;
      }
      
    }
  } else {
    $query = new QueryDb($dbaccess,"QueryDir");  
    $count = $query->Query(0,0,"TABLE", "select count(*) from fld, doc2 where fld.dirid=$dirid and childid=doc2.id");
    if (($query->nb > 0) && ($count[0]["count"] > 0)) return true;


    $count = $query->Query(0,0,"TABLE", "select count(*) from fld, doc5 where fld.dirid=$dirid and childid=doc5.id");
    if (($query->nb > 0) && ($count[0]["count"] > 0)) return true;

  }
  return false;
}




// --------------------------------------------------------------------
function GetClassesDoc($dbaccess,$userid,$classid=0,$qtype="LIST")
     // --------------------------------------------------------------------
{
  $query = new QueryDb($dbaccess,"DocFam");
  
  
  $query->AddQuery("doctype='C'");
  
  if ($classid >0 ) {
    $cdoc = new DocFam($dbaccess, $classid);
    $query->AddQuery("usefor = '".$cdoc->usefor."'");
  }
  
  
  $query->AddQuery("hasviewprivilege(".$userid.",docfam.profid)");
  $query->order_by="lower(title)";
  return $query->Query(0,0,$qtype);
}

 /**
 * return array of possible profil for profile type
 *
 * @param string $dbaccess database specification
 * @param int  $famid the id of family document
 * @return array/Doc
 * @see getChildDir()
 */
function GetProfileDoc($dbaccess,$docid,$defProfFamId="")
{
  global $action;
  $filter=array();
  
  $doc=new Doc($dbaccess,$docid);
  $chdoc=$doc->GetFromDoc();
  if ($defProfFamId=="") $defProfFamId=$doc->defProfFamId;
  
  $cond = GetSqlCond($chdoc,"dpdoc_famid");
  if ($cond != "") $filter[]="dpdoc_famid is null or (".GetSqlCond($chdoc,"dpdoc_famid").")";
  else $filter[]="dpdoc_famid is null";
  $filter[]="fromid=".$defProfFamId;
  $tcv = getChildDoc($dbaccess,
		     0,0,"ALL",$filter,$action->user->id,"TABLE",$defProfFamId);
  
  return $tcv;
}



?>
