<?php
// ---------------------------------------------------------------
// $Id: Lib.Dir.php,v 1.1 2002/03/15 16:02:53 eric Exp $
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


  function getChildDir($dbaccess, $dirid, $notfldsearch=false) {
    // query to find child directories (no recursive - only in the specified folder)


      if (!($dirid > 0)) return array();   
    if ($notfldsearch) $odoctype='D';
    else $odoctype='S';
    $qsql= "select distinct on (doc.id) * from doc  ".
      "where  ((doc.doctype='D') OR (doc.doctype='$odoctype')) ".
	"and doc.initid in (select childid from fld where ((qtype='S') or (qtype='F')) and (dirid=$dirid)) ";



    $query = new QueryDb($dbaccess,"Doc");
    $query -> AddQuery("dirid=".$dirid);

    $tableq=$query->Query(0,0,"LIST",$qsql);
    if ($query->nb == 0) return array();            

    return($tableq);
  }

  function getChildDoc($dbaccess, $dirid, $start="0", $slice="ALL", $sqlfilters=array()) {

    // query to find child documents


    if (count($sqlfilters)>0)    $sqlcond = "and (".implode(") and (", $sqlfilters).")";
    else $sqlcond = "";

    $fld = new Dir($dbaccess, $dirid);

    if ( $fld->classname != 'DocSearch') {

    $qsql= "select * ".
      "from doc  ".
	"where (doc.doctype != 'T')  ".
	  "and ((doc.initid in (select childid from fld where (qtype='S') and (dirid=$dirid)) and doc.locked != -1)".
	  "   or (doc.id in (select childid from fld where (qtype='F') and (dirid=$dirid))))".
	    $sqlcond.
	      " order by title LIMIT $slice OFFSET $start;";

    } else {
      // search familly
      $docsearch = new QueryDb($dbaccess,"QueryDir");
      $docsearch ->AddQuery("dirid=$dirid");
      $docsearch ->AddQuery("qtype='M'");
      $ldocsearch = $docsearch ->Query();
      

      // for the moment only one query search
	if (($docsearch ->nb) > 0) {
	  $qsql= "select * ".
	    "from doc  ".
	      "where (doc.doctype != 'T')  ".
		"and (doc.id in ({$ldocsearch[0]->query})) ".
		    $sqlcond.
		      " order by title LIMIT $slice OFFSET $start;";

	} else {
	  return array(); // no query avalaible
	}
    }
    

    // 	print "<HR>".$qsql;
    $query = new QueryDb($dbaccess,"Doc");


    $tableq=$query->Query(0,0,"LIST",$qsql);

    if ($query->nb == 0)
      {
	return array();
      }


    return($tableq);
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
  function getRChildDirId($dbaccess,$dirid, $level=0) {
  // --------------------------------------------------------------------
    // query to find child directories (RECURSIVE)


  if ($level > 20) exit; // limit recursivity

    $childs = getChildDirId($dbaccess, $dirid, true);
    $rchilds = $childs;

    if (count($childs) > 0) {

    while(list($k,$v) = each($childs)) 
	  {

	    $rchilds = array_merge($rchilds, getRChildDirId($dbaccess,$v,$level+1));
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
?>