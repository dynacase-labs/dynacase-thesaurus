<?php
// ---------------------------------------------------------------
// $Id: search.php,v 1.1 2002/02/05 16:34:07 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/search.php,v $
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
// $Log: search.php,v $
// Revision 1.1  2002/02/05 16:34:07  eric
// decoupage pour FREEDOM-LIB
//
// Revision 1.5  2001/12/21 13:58:35  eric
// modif pour incident
//
// Revision 1.4  2001/12/19 17:57:32  eric
// on continue
//
// Revision 1.3  2001/11/28 13:40:10  eric
// home directory
//
// Revision 1.2  2001/11/26 18:01:01  eric
// new popup & no lock for no revisable document
//
// Revision 1.1  2001/11/22 17:49:13  eric
// search doc
//
//
// ---------------------------------------------------------------

include_once("FDL/Class.DocSearch.php");
include_once("FDL/Class.Dir.php");
include_once("FDL/Class.QueryDir.php");
include_once("FDL/Class.QueryDirV.php");
include_once("FDL/freedom_util.php");  





// -----------------------------------
function search(&$action) {
  // -----------------------------------

  

  // Get all the params      
  $keyword=GetHttpVars("keyword"); // keyword to search
  $dirid=GetHttpVars("dirid"); // insert search in this folder
  $title=GetHttpVars("title", _("new search ").$keyword); // title of the search
  $latest=GetHttpVars("latest", false); // only latest revision
  $save=GetHttpVars("save", false); // the query need to be saved
  $sensitive=GetHttpVars("sensitive", false); // the keyword is case sensitive
  $fromdir=GetHttpVars("fromdir", false); // the keyword is case sensitive

  
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $sdoc = new DocSearch($dbaccess);
  $sdoc->revision = "0";
  $sdoc->owner = $action->user->id;
  $sdoc->locked = $action->user->id; // lock for next modification
  $sdoc->fileref = "0";
  if ($save)    $sdoc->doctype = 'S';// it is a search document
  else $sdoc->doctype = 'T';// it is a temporary document (will be delete after)
  $sdoc->cprofid = "0"; // NO CREATION PROFILE ACCESS
  $sdoc->useforprof = 'f';
  $sdoc->fromid = 5;
  $sdoc->title = $title;
  $sdoc->Add();
  $dbaccess = $action->GetParam("FREEDOM_DB");
  

  if ($save) { 
    // insert search folder in current folder
    $dir = new Dir($dbaccess, $dirid);
    $dir->AddFile($sdoc->id);

    // save attributes
    $oval = new DocValue($dbaccess);
    $oval ->docid = $sdoc->id;
    $oval ->attrid = QA_TITLE;
    $oval ->value = $title;
    $oval -> Add();


    $oval ->docid = $sdoc->id;
    $oval ->attrid = QA_KEY;
    $oval ->value = $keyword;
    $oval -> Add();

    $oval ->docid = $sdoc->id;
    $oval ->attrid = QA_LAST;
    if ($latest)    $oval ->value =_("yes");
    else    $oval ->value =_("no");
    $oval -> Add();

    $oval ->docid = $sdoc->id;
    $oval ->attrid = QA_CASE;
    if ($sensitive)    $oval ->value =_("yes");
    else    $oval ->value =_("no");
    $oval -> Add();
    $oval ->docid = $sdoc->id;
    $oval ->attrid = QA_FROM;
    if ($fromdir) {
      $oval ->value = $dir->title;
    }    else    $oval ->value =_("root folder");
    $oval -> Add();


  }

     
  

  if ($fromdir) {
    $oqdv = new QueryDirV($dbaccess);
    $cdirid = $oqdv->getRChildDirId($dirid);
    $cdirid[] = $dirid;
    
    $sql_fromdir = "and ".GetSqlCond($cdirid,"dirv.dirid");

  } else $sql_fromdir = "";




  if ($sensitive) $testval = "like '%$keyword%'";
  else $testval = "~* '.*$keyword.*'";
		    
  if ($fromdir) {
    if ($latest) {
      $query = "select distinct on (initid) * from doc, docvalue, dirv where (value $testval)  $sql_fromdir and (doc.id = docvalue.docid) and (doc.id = dirv.childid) order by initid, revision desc"; 
    } else {
      $query = "select distinct on (docid) docid as id from docvalue, dirv where (value $testval) $sql_fromdir and (docvalue.docid = dirv.childid)";

    }
  } else if ($latest) {
    $query = "select distinct on (initid) * from doc, docvalue where (value $testval) and (doc.id = docvalue.docid)  order by initid, revision desc"; 
  } else {
  $query = "select distinct docid as id from docvalue where (value $testval) ";

  }

  $sdoc-> AddQuery($query);


  redirect($action,GetHttpVars("app"),"FREEDOM_VIEW&dirid=".$sdoc->id);
  
  
}


?>