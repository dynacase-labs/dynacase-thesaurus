<?php
// ---------------------------------------------------------------
// $Id: generic_search.php,v 1.9 2002/11/07 16:00:00 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_search.php,v $
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
include_once("FDL/Class.DocUser.php");
include_once("FDL/freedom_util.php");  
include_once("GENERIC/generic_util.php");  





// -----------------------------------
function generic_search(&$action) {
  // -----------------------------------
   

  // Get all the params      
  $keyword=GetHttpVars("keyword"); // keyword to search
  $dirid=GetHttpVars("catg", getDefFld($action)); // folder where search

  
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc = new Doc($dbaccess, $dirid);

  $sdoc = createDoc($dbaccess,5); //new DocSearch($dbaccess);
  $sdoc->doctype = 'T';// it is a temporary document (will be delete after)
  $sdoc->title = sprintf(_("search %s"),$keyword);
  if ($doc->id == getDefFld($action)) $sdoc->title = sprintf(_("search  contains %s in all state"),$keyword );
  else $sdoc->title = sprintf(_("search contains %s in %s"),$keyword,$doc->title );

  $sdoc->Add();
  
  $searchquery="";
  $sdirid = 0;
  if ($doc->defDoctype == 'S') { // case of search in search doc
    $sdirid = $doc->id;
  } else { // case of search in folder
    if ($doc->id != getDefFld($action))
      $sdirid = $dirid;

  }

  $famid = getDefFam($action);


  $sqlfilter[]= "doctype='F'";
  $sqlfilter[]= "getdocvalues(doc$famid.id)~* '.*$keyword.*' ";

  $query=getSqlSearchDoc($dbaccess, 
			 $sdirid,  
			 $famid, 
			 $sqlfilter);

  $sdoc-> AddQuery($query);

  redirect($action,GetHttpVars("app"),"GENERIC_LIST&dirid=".$sdoc->id."&catg=$dirid");
  
  
}


?>