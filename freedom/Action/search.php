<?php
// ---------------------------------------------------------------
// $Id: search.php,v 1.2 2001/11/26 18:01:01 eric Exp $
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
// Revision 1.2  2001/11/26 18:01:01  eric
// new popup & no lock for no revisable document
//
// Revision 1.1  2001/11/22 17:49:13  eric
// search doc
//
//
// ---------------------------------------------------------------

include_once("FREEDOM/Class.DocSearch.php");
include_once("FREEDOM/Class.QueryDir.php");
include_once("FREEDOM/Class.QueryDirV.php");
include_once("FREEDOM/freedom_util.php");  





// -----------------------------------
function search(&$action) {
  // -----------------------------------

  

  // Get all the params      
  $keyword=GetHttpVars("keyword"); // keyword to search
  $dir=GetHttpVars("dirid"); // insert search in this folder
  $title=GetHttpVars("title", _("new search ").$keyword); // title of the search
  $latest=GetHttpVars("latest", false); // only latest revision
  $save=GetHttpVars("save", false); // the query need to be saved
  $sensitive=GetHttpVars("sensitive", false); // the keyword is case sensitive


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
  

  if ($save) { // save attributes
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


  }
  // insert search folder in current folder
  $oqd = new QueryDir($dbaccess);
  $oqd->dirid = $dir;
  $oqd->qtype="Q"; // query search
  $oqd->query = "select id from doc where id=".$sdoc->id;
  $oqd-> Add();

  //  $oqdv = new QueryDirV($dbaccess);
  //$oqdv->getRChildDir($dir);

  // insert query in search folder 
  $oqd = new QueryDir($dbaccess);
  $oqd->id = "";
  //  $oqd->DeleteDir($sdoc->id);
  $oqd->dirid = $sdoc->id;
  $oqd->qtype="M"; // multiple


  if ($sensitive) $testval = "like '%$keyword%'";
  else $testval = "~* '.*$keyword.*'";
		    

  if ($latest) {
    $oqd->query = "select distinct on (initid) * from doc, docvalue where value $testval and doc.id = docvalue.docid order by initid, revision desc"; 
  } else {
  $oqd->query = "select distinct docid as id from docvalue where value $testval";

  }
  $oqd-> Add();

  $action->log->Debug("oqdv finish");

  redirect($action,GetHttpVars("app"),"FREEDOM_LIST&dirid=".$sdoc->id);
  
  
}


?>