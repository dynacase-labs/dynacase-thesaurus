<?php
// ---------------------------------------------------------------
// $Id: generic_tab.php,v 1.4 2002/09/02 16:38:49 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_tab.php,v $
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
include_once("FDL/freedom_util.php");  
include_once("GENERIC/generic_util.php");





// -----------------------------------
function generic_tab(&$action) {
  // -----------------------------------

  

  // Get all the params      
  $keyword=GetHttpVars("keyword"); // keyword to search
  $dirid=GetHttpVars("catg", getDefFld($action)); // folder where search
  $tab=GetHttpVars("tab", 1); // tab index

  

  // hightlight the selected part (ABC, DEF, ...)
  $tabletter=array("", "ABC","DEF", "GHI","JKL","MNO","PQRS","TUV","WXYZ");


  $dbaccess = $action->GetParam("FREEDOM_DB");

  $dir = new Dir($dbaccess, $dirid);


  $sdoc = new DocSearch($dbaccess);


  $sdoc->doctype = 'T';// it is a temporary document (will be delete after)

  if ($dir->id == getDefFld($action))   {
    $sdoc->title = sprintf(_("%s all categories "),$tabletter[$tab] );
    $qfld = "";
  }  else {
    $sdoc->title = sprintf(_("%s %s category "),$tabletter[$tab],$dir->title );
    $qfld = "and doc.initid in (select childid from fld where childid=doc.id and dirid=$dirid) ";
  }


  $ldoc = $sdoc->GetDocWithSameTitle();

 

  $sdoc->Add();
  $qtitle = ($tabletter[$tab]=="")?"":"and (title ~* '^[".$tabletter[$tab]."].*') ";

  $famid = getDefFam($action);
  $sqlfrom = getSqlFrom($dbaccess,$famid);

  $query = "select * from doc where $sqlfrom ".
    $qtitle.
     $qfld.
     "and (locked != -1) ".
     "and (not useforprof) ".
     "and (doctype='F')";


  $sdoc-> AddQuery($query);
  

  redirect($action,GetHttpVars("app"),"GENERIC_LIST&tab=$tab&dirid=".$sdoc->id."&catg=$dirid");
  
  
}


?>