<?php

// ---------------------------------------------------------------
// $Id: freedom_util.php,v 1.31 2003/02/07 17:31:49 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Fdl/freedom_util.php,v $
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






// return document object in type concordance
function newDoc(&$doc,$dbaccess, $id='',$res='',$dbid=0) {

  global $gdocs;// optimize for speed

  
  if ($dbaccess=="") {
    // don't test if file exist or must be searched in include_path 
    include("dbaccess.php");
           
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
  if ($id > 0) {

     if (isset($gdocs[$id])) {
       $doc = $gdocs[$id]; // optimize for speed
       return true;
     }
    global $CORE_DBID;
	if (!isset($CORE_DBID) || !isset($CORE_DBID["$dbaccess"])) {
           $CORE_DBID["$dbaccess"] = pg_connect("$dbaccess");
        } 
    $dbid=$CORE_DBID["$dbaccess"];

    $result = pg_exec($dbid,"select classname, fromid, doctype from doc where id=$id;");
    if (pg_numrows ($result) > 0) {
      $arr = pg_fetch_array ($result, 0);
      $fromid= $arr[1];
      $doctype= $arr[2];
      if ($doctype=="C") $classname="DocFam"; 
      else if ($fromid == 0) $classname= $arr[0];
      else {$classname="Doc$fromid";$gen="GEN";}
    }
  } else if ($res != '') {
    $fromid=$res["fromid"];
    $doctype=$res["doctype"];
    if ($doctype=="C") $classname= "DocFam"; 
    else if ($fromid > 0) {$classname= "Doc".$res["fromid"];$gen="GEN";}
    else  $classname=$res["classname"];
  }
	    
  if ($classname != "") {
    include_once("FDL$gen/Class.$classname.php");
    //    print "new $classname($dbaccess, $id, $res, $dbid)<BR>";
    $doc=new $classname($dbaccess, $id, $res, $dbid);
    if ($id > 0)  $gdocs[$id]=&$doc;

    return (true);
  } else {
    include_once("FDL/Class.DocFile.php");
    $doc=new DocFile($dbaccess, $id, $res, $dbid);

    return (true);
  }
} 


// create a new document object in type concordance
function createDoc($dbaccess,$fromid,$control=true) {

  if ($fromid > 0) {
    $cdoc = new Doc($dbaccess, $fromid);

    if ($control) {
      $err = $cdoc->control('view');
      if ($err != "") return false;
    }

    
    $classname = "Doc".$fromid;
    include_once("FDLGEN/Class.$classname.php");
    $doc = new $classname($dbaccess);
    
    $doc->revision = "0";
    $doc->fileref = "0";
    $doc->doctype = $doc->defDoctype;// it is a new  document (not a familly)
    $doc->cprofid = "0"; // NO CREATION PROFILE ACCESS

    $doc->fromid = $fromid;
    $doc->setProfil($cdoc->cprofid); // inherit from its familly	
    $doc->icon = $cdoc->icon; // inherit from its familly	
    $doc->usefor = $cdoc->usefor; // inherit from its familly
    $doc->wid=$cdoc->wid;
    $nattr = $cdoc->GetNormalAttributes();
    while (list($k,$v) = each($nattr)) {
	$aid = $v->id;
	//	print $aid.$cdoc->getValue($aid);
	//$doc->setValue($aid, $cdoc->getValue($aid));
	$doc->$aid = $doc->GetValueMethod($cdoc->getValue($aid));

    }              
    return ($doc);
    
  }
  return new Doc($dbaccess);

}

// use to usort attributes
function tordered($a, $b) {
  
  if (isset($a->ordered) && isset($b->ordered)) {
	if (intval($a->ordered) == intval($b->ordered)) return 0;
	if (intval($a->ordered) > intval($b->ordered)) return 1;
	return -1;
  }
  if (isset($a->ordered) ) return -1;
  if (isset($b->ordered) ) return 1;
  return 0;
	
}



function getFamIdFromName($dbaccess, $name) {
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

function setFamidInLayout(&$action) {
  
  global $tFamIdName;

  if (! isset($tFamIdName))  getFamIdFromName($action->GetParam("FREEDOM_DB"),"-");
  
  reset($tFamIdName);
  while(list($k,$v) = each($tFamIdName)) {
    $action->lay->set("IDFAM_$k", $v);
  }
}
?>
