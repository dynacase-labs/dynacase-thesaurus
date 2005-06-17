<?php
/**
 * Function Utilities for freedom
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_util.php,v 1.68 2005/06/17 07:51:33 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

include_once("FDL/fdl_xml.php");

//

// ------------------------------------------------------
// construction of a sql disjonction
// ------------------------------------------------------
function GetSqlCond2($Table, $column) 
// ------------------------------------------------------
{
  $sql_cond="";
  if (count($Table) > 0)
    {
      $sql_cond = "(($column = '$Table[0]') ";
      for ($i=1; $i< count($Table); $i++)
	{
	  $sql_cond = $sql_cond."OR ($column = '$Table[$i]') ";
	}
      $sql_cond = $sql_cond.")";
    }

  return $sql_cond;
}


function GetSqlCond($Table, $column, $integer=false) 
// ------------------------------------------------------
{
  $sql_cond="";
  if (count($Table) > 0)
    {
      if ($integer) { // for integer type 
	$sql_cond = "$column in (";      
	$sql_cond .= implode(",",$Table);
	$sql_cond .= ")";
      } else {// for text type 
	$sql_cond = "$column in ('";      
	$sql_cond .= implode("','",$Table);
	$sql_cond .= "')";
      }
    }

  return $sql_cond;
}


/** 
 * optimize for speed : memorize object for future use
 * @global array $_GLOBALS["gdocs"] 
 * @name $gdocs
 */


/**
 * return document object in type concordance
 * @param Doc &$doc empty object document
 * @param string $dbaccess database specification
 * @param int $id identificator of the object
 * @param array $res array of result issue to QueryDb {@link QueryDb::Query()}
 * @param resource $dbid the database connection resource
 * @global array optimize for speed 
 * 
 * @return bool false if error occured
 */
function newDoc(&$doc,$dbaccess, $id='',$res='',$dbid=0) {

  global $gdocs;// optimize for speed

  
  if ($dbaccess=="") {
    // don't test if file exist or must be searched in include_path 
    $dbaccess=getDbAccess();
           
  }
  //    print("doctype:".$res["doctype"]);
  $classname="";
  if (($id == '') && ($res == "")) {
    include_once("FDL/Class.DocFile.php");
    $doc=new DocFile($dbaccess);

    return (true);
  }
  $fromid="";
  $gen=""; // path GEN or not
  if (! is_numeric($id)) $id=getIdFromName($dbaccess,$id);

  $id=intval($id);
  if ($id > 0) {

    if (isset($gdocs[$id])) {
      $doc = $gdocs[$id]; // optimize for speed
      return true;
    }
  
    $dbid=getDbid($dbaccess);

  //    print("doctype:".$res["doctype $dbaccess $dbid";
    $fromid= getFromId($dbaccess,$id);
    if ($fromid > 0) {
      $classname= "Doc$fromid";
      $gen=getGen($dbaccess);
    }else if ($fromid == -1) $classname="DocFam"; 
    

    
  } else if ($res != '') {
    $fromid=$res["fromid"];
    $doctype=$res["doctype"];
    if ($doctype=="C") $classname= "DocFam"; 
    else if ($fromid > 0) {$classname= "Doc".$res["fromid"];$gen=getGen($dbaccess);}
    else  $classname=$res["classname"];
  }
	    
  if ($classname != "") {
    include_once("FDL$gen/Class.$classname.php");
    $doc=new $classname($dbaccess, $id, $res, $dbid);
    if (($id > 0) && (count($gdocs) < MAXGDOCS))    $gdocs[$id]=&$doc;

    return (true);
  } else {
    include_once("FDL/Class.DocFile.php");
    $doc=new DocFile($dbaccess, $id, $res, $dbid);

    return (true);
  }
} 


/**
 * create a new document object in type concordance
 *
 * the document is set with default values and default profil of the family 
 * @param string $dbaccess database specification
 * @param string $fromid identificator of the family document (the number or internal name)
 * @param bool $control if false don't control the user hability to create this kind of document
 * @return Doc may be return false if no hability to create the document
 */
function createDoc($dbaccess,$fromid,$control=true) {

  if (! is_numeric($fromid)) $fromid=getFamIdFromName($dbaccess,$fromid);
  if ($fromid > 0) {
    include_once("FDL/Class.DocFam.php");
    $cdoc = new DocFam($dbaccess, $fromid);

    if ($control) {
      $err = $cdoc->control('create');
      if ($err != "") return false;
    }

    
    $classname = "Doc".$fromid;
    $GEN=getGen($dbaccess);
    include_once("FDL$GEN/Class.$classname.php");
    $doc = new $classname($dbaccess);
    
    $doc->revision = "0";
    $doc->fileref = "0";
    $doc->doctype = $doc->defDoctype;// it is a new  document (not a familly)
    $doc->cprofid = "0"; // NO CREATION PROFILE ACCESS

    $doc->fromid = $fromid;
    $doc->setProfil($cdoc->cprofid); // inherit from its familly
    $doc->setCvid($cdoc->ccvid); // inherit from its familly	
    $doc->icon = $cdoc->icon; // inherit from its familly	
    $doc->usefor = $cdoc->usefor; // inherit from its familly
    $doc->wid=$cdoc->wid;
    $doc->atags=$cdoc->atags;
    
    $doc->setDefaultValues($cdoc->getDefValues());
    $doc->ApplyMask();
    return ($doc);
    
  }
  return new Doc($dbaccess);

}
/**
 * return document table value
 * @param string $dbaccess database specification
 * @param int $id identificator of the object
 * 
 * @return array false if error occured
 */
function getFromId($dbaccess, $id) {

  if (!($id > 0)) return false;
  if (! is_numeric($id)) return false;
  $dbid=getDbid($dbaccess);   
  $fromid=false;
  $result = pg_query($dbid,"select  fromid from docfrom where id=$id;");

  if (pg_numrows ($result) > 0) {
    $arr = pg_fetch_array ($result, 0,PGSQL_ASSOC);

    $fromid= $arr["fromid"];
  }
  
  return $fromid;    
} 
/**
 * return document table value
 * @param string $dbaccess database specification
 * @param int $id identificator of the object
 * @param array $sqlfilters add sql supply condition
 * 
 * @return array false if error occured
 */
function getTDoc($dbaccess, $id,$sqlfilters=array()) {
  global $action;
  global $SQLDELAY,$SQLDEBUG;

  if (!($id > 0)) return false;
  $dbid=getDbid($dbaccess);   
  $table="doc";
  $fromid= getFromId($dbaccess, $id);
  if ($fromid > 0) $table="doc$fromid";
  else if ($fromid == -1) $table="docfam";

  $sqlcond="";
  if (count($sqlfilters)>0)    $sqlcond = "and (".implode(") and (", $sqlfilters).")";

  $userid=$action->user->id;
  if ($SQLDEBUG) $sqlt1=microtime(); // to test delay of request
  $sql="select *,getuperm($userid,profid) as uperm from only $table where id=$id $sqlcond;";
  $result = pg_query($dbid,$sql); 
  if ($SQLDEBUG) {
       global $TSQLDELAY;
       $SQLDELAY+=microtime_diff(microtime(),$sqlt1);// to test delay of request
       $TSQLDELAY[]="t=>".microtime_diff(microtime(),$sqlt1)."s=>$sql";
  }
  if (($result) && (pg_numrows ($result) > 0)) {
    $arr = pg_fetch_array ($result, 0, PGSQL_ASSOC);

    return $arr;
  }
  return false;  
} 
/**
 * return the value of an array item
 *
 * @param array $t the array where get value
 * @param string $k the index of the value
 * @param string $d default value if not found or if it is empty
 * @return string
 */
function getv(&$t,$k,$d="") {
  if (isset($t[$k]) && ($t[$k] != "")) return $t[$k];
  if (strpos($t["attrids"],"£$k") !== 0) {
    
    $tvalues = explode("£",$t["values"]);
    $tattrids = explode("£",$t["attrids"]);
      
    while(list($ka,$va) = each($tattrids)) {      
      if (!isset($t[$va])) $t[$va]=$tvalues[$ka];
      if ($va == $k) {
	if ($tvalues[$ka]!="") return $tvalues[$ka];
	break;
      }
    }
  }
  return $d;
}

/** 
 * use to usort attributes
 * @param BasicAttribute $a
 * @param BasicAttribute $b
 */
function tordered($a, $b) {
  
  if (isset($a->ordered) && isset($b->ordered)) {
	if (intval($a->ordered) == intval($b->ordered)) return 0;
	if (intval($a->ordered) > intval($b->ordered)) return 1;
	return -1;
  }
  if (isset($a->ordered) ) return 1;
  if (isset($b->ordered) ) return -1;
  return 0;
	
}


/**
 * return the identificator of a family from internal name
 *
 * @param string $dbaccess database specification
 * @param string $name internal family name

 * @return int 0 if not found
 */
function getFamIdFromName($dbaccess, $name) {
  include_once("FDL/Class.DocFam.php");
  global $tFamIdName;

  if (! isset($tFamIdName)) {
    $q = new QueryDb($dbaccess, "DocFam");
    $ql=$q->Query(0,0,"TABLE");
    
    while(list($k,$v) = each($ql)) {
      if ($v["name"] != "") $tFamIdName[$v["name"]]=$v["id"];
    }
  }

  if (isset($tFamIdName[$name])) return $tFamIdName[$name];
  return 0; 
  
}
/**
 * return the identificator of a document from its logical name
 *
 * @param string $dbaccess database specification
 * @param string $name logical name
 * @param string $famid must be set to increase speed search

 * @return int 0 if not found, return negative first id found if multiple (name must be unique)
 */
function getIdFromName($dbaccess, $name, $famid="") {
  $dbid=getDbid($dbaccess);   
  $id=false;
  $result = pg_query($dbid,"select id from docname where name='$name';");
  $n=pg_numrows ($result);
  if ($n > 0) {
    $arr = pg_fetch_array ($result,($n-1),PGSQL_ASSOC);
    $id= $arr["id"];
  }    
  return $id;  
}
function setFamidInLayout(&$action) {
  
  global $tFamIdName;

  if (! isset($tFamIdName))  getFamIdFromName($action->GetParam("FREEDOM_DB"),"-");
  
  reset($tFamIdName);
  while(list($k,$v) = each($tFamIdName)) {
    $action->lay->set("IDFAM_$k", $v);
  }
}

/**
 * return freedom user document in concordance with what user id
 * @param string $dbaccess database specification
 * @param int $userid what user identificator 
 * @return Doc the user document
 */
function getDocFromUserId($dbaccess,$userid) {
  if ($userid == "") return false;
  include_once("FDL/Lib.Dir.php");
  $tdoc=array();
  $user = new User("",$userid);
  if (! $user->isAffected()) return false;
  if ($user->isgroup == "Y") {
    $filter = array("us_whatid = $userid");
    $tdoc = getChildDoc($dbaccess, 0,0,"ALL", $filter,1,"LIST",
			getFamIdFromName($dbaccess,"IGROUP"));
  } else {
    $filter = array("us_whatid = $userid");
    $tdoc = getChildDoc($dbaccess, 0,0,"ALL", $filter,1,"LIST",
			getFamIdFromName($dbaccess,"IUSER"));
  }
  if (count($tdoc) == 0) return false;
  return $tdoc[0];
}


function ComputeVisibility($vis, $fvis) {
  if ($vis == "I") return $vis;
  if ($fvis == "H") return $fvis;
  if (($fvis == "R") && ($vis == "W") ) return $fvis;
  if (($fvis == "R") && ($vis == "O")) return "H";

  return $vis;

}

/**
 * return doc array of latest revision of initid
 *
 * @param string $dbaccess database specification
 * @param string $initid initial identificator of the  document 
 * @param array $sqlfilters add sql supply condition
 * @return array values array if found. False if initid not avalaible
 */
function getLatestTDoc($dbaccess, $initid,$sqlfilters=array()) {
  global $action;

  if (!($initid > 0)) return false;
  $dbid=getDbid($dbaccess);   
  $table="doc";
  $fromid= getFromId($dbaccess, $initid);
  if ($fromid > 0) $table="doc$fromid";
  else if ($fromid == -1) $table="docfam";
    
  $sqlcond="";
  if (count($sqlfilters)>0)    $sqlcond = "and (".implode(") and (", $sqlfilters).")";

  $userid=$action->user->id;
  $result = pg_exec($dbid,"select *,getuperm($userid,profid) as uperm  from only $table where initid=$initid and locked != -1 $sqlcond;");
  if (pg_numrows ($result) > 0) {
    $arr = pg_fetch_array ($result, 0, PGSQL_ASSOC);

    return $arr;
  }
  return false;  
} 

/**
 * Create default folder for a family with default constraint
 *
 * @param Doc $Doc the family object document
 * @return int id of new folder (false if error)
 */
function createAutoFolder(&$doc) {
    $dir = createDoc($doc->dbaccess, getFamIdFromName($doc->dbaccess,"DIR"));
    $err=$dir->Add();
    if ($err!="") return false;
    $dir->setValue("BA_TITLE",sprintf(_("root for %s"),$doc->title));
    $dir->setValue("BA_DESC",_("default folder"));
    $dir->setValue("FLD_ALLBUT","1");
    $dir->setValue("FLD_FAM",$doc->title."\n"._("folder")."\n"._("search"));
    $dir->setValue("FLD_FAMIDS",$doc->id."\n".getFamIdFromName($doc->dbaccess,"DIR").
		   "\n".getFamIdFromName($doc->dbaccess,"SEARCH"));
    $dir->Modify();
    $fldid=$dir->id;
    return $fldid;
  
}



?>
