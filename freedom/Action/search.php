<?php
// ---------------------------------------------------------------
// $Id: search.php,v 1.1 2001/11/22 17:49:13 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Attic/search.php,v $
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
// Revision 1.1  2001/11/22 17:49:13  eric
// search doc
//
//
// ---------------------------------------------------------------

include_once("FREEDOM/Class.DocSearch.php");
include_once("FREEDOM/Class.QueryDir.php");
include_once("FREEDOM/freedom_util.php");  





// -----------------------------------
function search(&$action) {
  // -----------------------------------

  

  // Get all the params      
  $keyword=GetHttpVars("keyword"); // keyword to search
  $dir=GetHttpVars("dirid"); // insert search in this folder
  $title=GetHttpVars("title", _("new search ").$keyword); // title of the search


  $dbaccess = $action->GetParam("FREEDOM_DB");

  $sdoc = new DocSearch($dbaccess);
  $sdoc->revision = "0";
  $sdoc->owner = $action->user->id;
  $sdoc->locked = $action->user->id; // lock for next modification
  $sdoc->fileref = "0";
  $sdoc->doctype = 'S';// it is a search document
  $sdoc->cprofid = "0"; // NO CREATION PROFILE ACCESS
  $sdoc->useforprof = 'f';
  $sdoc->fromid = 5;
  $sdoc->title = $title;
  $sdoc->Add();
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  // insert search folder in current folder
  $oqd = new QueryDir($dbaccess);
  $oqd->dirid = $dir;
  $oqd->qtype="Q"; // query search
  $oqd->query = "select id from doc where id=".$sdoc->id;
  $oqd-> Add();



  // insert query in search folder 
  $oqd = new QueryDir($dbaccess);
  $oqd->id = "";
  //  $oqd->DeleteDir($sdoc->id);
  $oqd->dirid = $sdoc->id;
  $oqd->qtype="M"; // multiple
  $oqd->query = "select distinct docid as id from docvalue where value like '%$keyword%'";

  $oqd-> Add();


  redirect($action,GetHttpVars("app"),"FREEDOM_LIST&dirid=".$sdoc->id);
  
  
}


?>