<?php

// ---------------------------------------------------------------
// $Id: freedom_util.php,v 1.19 2002/11/04 17:56:17 eric Exp $
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


function GetSqlCond($Table, $column) 
// ------------------------------------------------------
{
  $sql_cond="";
  if (count($Table) > 0)
    {
      $sql_cond = "$column in ('$Table[0]'";
      for ($i=1; $i< count($Table); $i++)
	{
	  $sql_cond .= ",'$Table[$i]'";
	}
      $sql_cond .= ")";
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
      else $classname="Doc$fromid";
    }
  } else if ($res != '') {
    $fromid=$res["fromid"];
    $doctype=$res["doctype"];
    if ($doctype=="C") $classname= "DocFam"; 
    else if ($fromid > 0) $classname= "Doc".$res["fromid"];
    else  $classname=$res["classname"];
  }
	    
  if ($classname != "") {
    include_once("FDL/Class.$classname.php");
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
function createDoc($dbaccess,$fromid) {

  if ($fromid > 0) {
    $cdoc = new Doc($dbaccess, $fromid);

    $err = $cdoc->control('view');
    if ($err != "") return false;

    
    $classname = "Doc".$fromid;
    include_once("FDL/Class.$classname.php");
    $doc = new $classname($dbaccess);
    
    $doc->revision = "0";
    $doc->fileref = "0";
    //$doc->doctype = 'F';// it is a new  document (not a familly)
    $doc->cprofid = "0"; // NO CREATION PROFILE ACCESS
    $doc->useforprof = 'f';
    $doc->fromid = $fromid;
    $doc->profid = $cdoc->cprofid; // inherit from its familly	
    $doc->icon = $cdoc->icon; // inherit from its familly	
    $doc->useforprof = $cdoc->useforprof; // inherit from its familly
    $doc->wid=$cdoc->wid;

    return ($doc);
    
  }
  return newDoc($dbaccess);

}

// use to usort attributes
function tordered($a, $b) {
  if (isset($a->ordered) && isset($b->ordered)) {
	if ($a->ordered == $b->ordered) return 0;
	if ($a->ordered > $b->ordered) return 1;
  }
  return -1;
	
}

?>
