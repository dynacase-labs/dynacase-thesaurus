<?php
// ---------------------------------------------------------------
// $Id: search.php,v 1.6 2002/09/02 16:38:49 eric Exp $
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


include_once("FDL/Class.DocSearch.php");
include_once("FDL/Class.Dir.php");
include_once("FDL/Class.QueryDir.php");
include_once("FDL/freedom_util.php");  





// -----------------------------------
function search(&$action) {
  // -----------------------------------

  

  // Get all the params      
  $keyword=GetHttpVars("keyword"); // keyword to search
  $dirid=GetHttpVars("dirid"); // insert search in this folder
  $title=GetHttpVars("title"); // title of the search
  $latest=GetHttpVars("latest", false); // only latest revision
  $save=GetHttpVars("save", false); // the query need to be saved
  $sensitive=GetHttpVars("sensitive", false); // the keyword is case sensitive
  $fromdir=GetHttpVars("fromdir", false); // the keyword is case sensitive
  $famid=GetHttpVars("famid",0); // famid restrictive familly

  if ($title == "") $title=_("new search ").$keyword;
  
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


    $oval ->docid = $sdoc->id;
    $oval ->attrid = "SE_FAMID";
    $oval ->value = $famid;
    $oval -> Add();
  }

     
  

  if ($fromdir) {

    $cdirid = getRChildDirId($dbaccess, $dirid);
    $cdirid[] = $dirid;
    
    $sql_fromdir = GetSqlCond($cdirid,"dirid");

  } else $sql_fromdir = "";


    if ($latest)       $sqllatest = "and (doc.locked != -1)";
    else   $sqllatest = "";


  if ($sensitive) $testval = "like '%$keyword%'";
  else $testval = "~* '.*$keyword.*'";

  if ($famid) $sqlfam = "(doc.fromid = $famid) and";
  else $sqlfam = "";
		    
  if ($fromdir) {
    
    $query = "select doc.*  from doc, docvalue where  $sqlfam (value $testval)  and (doc.id in (select childid from fld where $sql_fromdir)) and (doc.id = docvalue.docid) $sqllatest "; 
    
  } else  {
    $query = "select doc.* from doc, docvalue where $sqlfam (value $testval) and (doc.id = docvalue.docid) $sqllatest"; 
  } 

  $sdoc-> AddQuery($query);


  redirect($action,GetHttpVars("app"),"FREEDOM_VIEW&dirid=".$sdoc->id);
  
  
}


?>