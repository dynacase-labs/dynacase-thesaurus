<?php

// ---------------------------------------------------------------
// $Id: freedom_util.php,v 1.8 2001/12/21 13:58:35 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Attic/freedom_util.php,v $
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
// $Log: freedom_util.php,v $
// Revision 1.8  2001/12/21 13:58:35  eric
// modif pour incident
//
// Revision 1.7  2001/12/19 17:57:32  eric
// on continue
//
// Revision 1.6  2001/12/18 09:18:10  eric
// first API with ZONE
//
// Revision 1.5  2001/12/13 17:45:01  eric
// ajout attribut classname sur les doc
//
// Revision 1.4  2001/11/22 17:49:13  eric
// search doc
//
// Revision 1.3  2001/11/21 08:38:58  eric
// ajout historique + modif sur control object
//
// Revision 1.2  2001/11/15 17:51:50  eric
// structuration des profils
//
// Revision 1.1  2001/11/09 09:41:14  eric
// gestion documentaire
//
// Revision 1.4  2001/10/03 15:56:03  eric
// ajout type date pour roaming
//
// Revision 1.3  2001/06/22 09:46:12  eric
// support attribut multimédia
//
// Revision 1.2  2001/06/19 16:08:17  eric
// correction pour type image
//
// Revision 1.1  2001/06/13 14:39:53  eric
// Freedom address book
//
// ---------------------------------------------------------------

include_once("FREEDOM/Class.Doc.php");
include_once("FREEDOM/Class.DocAttr.php");
include_once("FREEDOM/Class.DocValue.php");

// ------------------------------------------------------
// construction of a sql disjonction
// ------------------------------------------------------
function GetSqlCond($Table, $column) 
// ------------------------------------------------------
{
  $sql_cond="";
  if (count($Table) > 0)
    {
      $sql_cond = "(($column = $Table[0]) ";
      for ($i=1; $i< count($Table); $i++)
	{
	  $sql_cond = $sql_cond."OR ($column = $Table[$i]) ";
	}
      $sql_cond = $sql_cond.")";
    }

  return $sql_cond;
}


// ------------------------------------------------------
function GetTitleF($dbaccess,$docid)
// ------------------------------------------------------
{
  
  // ------------------------------------------------------
  // construction of TITLE
  // ------------------------------------------------------
  // construction of SQL condition to find title attributes

  
  
      $bdattr = new DocAttr($dbaccess);
      $titleTable = $bdattr->GetTitleIds();
      $sql_cond_title = $sql_cond_abs = GetSqlCond($titleTable,"attrid");
  


  $query_val = new QueryDb($dbaccess,"DocValue");
  



    // search title for freedom item
 $query_val->basic_elem->sup_where=array ("(docid=$docid)",
					  $sql_cond_title);

 $tablevalue = $query_val->Query();
 $title = "";
 for ($i=0; $i < $query_val->nb; $i++)
   {
     $title = chop($title.$tablevalue[$i]->value)." ";
   }

 return $title;
}






// -----------------------------------
function freedom_get_attr_card($dbaccess, $docid,&$title, &$tattr) {
  // -----------------------------------
  //  return the title and array of attribute values for a particular card
  // -----------------------------------
  

  // search title for freedom item

  static $query_val,$bdattr;
  static $first = 1;

  if ($first) {
    $query_val = new QueryDb($dbaccess,"DocValue");
    $bdattr = new DocAttr($dbaccess);
    $first = 0;
  }
  $title=GetTitleF($dbaccess,$docid);


	  
      // search values for freedom item
      $query_val->basic_elem->sup_where[0]="(docid=$docid)";

      $tablevalue = $query_val->Query();

      // Set the table elements

      $tattr=array();
      for ($i=0; $i < $query_val->nb; $i++)
	{
	
	  $lvalue = chop($tablevalue[$i]->value);

	  if ($lvalue != "")
	    {
	      $oattr=$bdattr-> GetAttribute($tablevalue[$i]->attrid);
	      
	      switch ($oattr->type)
		{
	      
		case "application": 
		case "embed": 
		case "image": 
		  // not supported in this version
		  break;
		default : 
		  
		  $tattr[$tablevalue[$i]->attrid]=$lvalue;
 
		  
		  break;
		
		}

	    }
	}
}


// return document object in type concordance
function newDoc($dbaccess, $id='',$res='',$dbid=0) {

  
  if ($dbaccess=="") {
    // don't test if file exist or must be searched in include_path 
    include("dbaccess.php");
           
  }

  //    print("doctype:".$res["doctype"]);
  $classname="";
  if (($id == '') && ($res == "")) {
    include_once("FREEDOM/Class.DocFile.php");
    return new DocFile($dbaccess);
  }
  $fromid="";
  if ($id != '') {
    global $CORE_DBID;
	if (!isset($CORE_DBID) || !isset($CORE_DBID["$dbaccess"])) {
           $CORE_DBID["$dbaccess"] = pg_connect("$dbaccess");
        } 
    $dbid=$CORE_DBID["$dbaccess"];

    $result = pg_exec($dbid,"select classname from doc where id=$id;");
    if (pg_numrows ($result) > 0) {
      $arr = pg_fetch_array ($result, 0);
      $classname= $arr[0];
    }
  } else if ($res != '') $classname=$res["classname"];
	    
  if ($classname != "") {
    include_once("FREEDOM/Class.$classname.php");
      return (new $classname($dbaccess, $id, $res, $dbid));
  } else {
    include_once("FREEDOM/Class.DocFile.php");
      return (new DocFile($dbaccess, $id, $res, $dbid));
  }
} 


// create a new document object in type concordance
function createDoc($dbaccess,$fromid) {

  if ($fromid > 0) {
    $cdoc = new Doc($dbaccess, $fromid);
    $classname = $cdoc->classname;
    include_once("FREEDOM/Class.$classname.php");
    $doc = new $classname($dbaccess);
    
    $doc->revision = "0";
    $doc->fileref = "0";
    //$doc->doctype = 'F';// it is a new  document (not a familly)
    $doc->cprofid = "0"; // NO CREATION PROFILE ACCESS
    $doc->useforprof = 'f';
    $doc->fromid = $fromid;
    $doc->profid = $cdoc->cprofid; // inherit from its familly	
    $doc->useforprof = $cdoc->useforprof; // inherit from its familly	
       return ($doc);
    
  }
  return new Doc($dbaccess);

}



?>
