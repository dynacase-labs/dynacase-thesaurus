<?php
// ---------------------------------------------------------------
// $Id: usercard_tab.php,v 1.2 2002/02/22 15:34:54 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Usercard/usercard_tab.php,v $
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





// -----------------------------------
function usercard_tab(&$action) {
  // -----------------------------------

  

  // Get all the params      
  $keyword=GetHttpVars("keyword"); // keyword to search
  $dirid=GetHttpVars("catg", TOP_USERDIR); // folder where search
  $tab=GetHttpVars("tab", 1); // tab index

  // hightlight the selected part (ABC, DEF, ...)
  $tabletter=array("A-Z", "ABC","DEF", "GHI","JKL","MNO","PQRS","TUV","WXYZ");


  $dbaccess = $action->GetParam("FREEDOM_DB");

  $dir = new Dir($dbaccess, $dirid);


  $sdoc = new DocSearch($dbaccess);


  $sdoc->doctype = 'T';// it is a temporary document (will be delete after)

  if ($dir->id == TOP_USERDIR)   $sdoc->title = sprintf(_("%s : all categories "),$tabletter[$tab] );
  else  $sdoc->title = sprintf(_("%s : %s category "),$tabletter[$tab],$dir->title );


  $ldoc = $sdoc->GetDocWithSameTitle();

  if (count($ldoc) > 0) {
    $sdoc=$ldoc[0]; // optimization :  use oldest same query
  } else {

  $sdoc->Add();

  $query = "select id from doc where (className = 'DocUser') ".
     "and (title ~* '^[".$tabletter[$tab]."].*') ".
     "and doc.id in (select childid from fld where dirid=$dirid) ".
     "and (locked != -1) ".
     "and (not useforprof) ".
     "and (doctype='F')";

  $sdoc-> AddQuery($query);
  }

  redirect($action,GetHttpVars("app"),"USERCARD_LIST&tab=$tab&dirid=".$sdoc->id."&catg=$dirid");
  
  
}


?>