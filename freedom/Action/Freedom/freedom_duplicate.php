<?php
// ---------------------------------------------------------------
// $Id: freedom_duplicate.php,v 1.7 2002/08/20 14:05:09 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_duplicate.php,v $
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


include_once("FDL/duplicate.php");

include_once("FDL/Class.Dir.php");


// -----------------------------------
function freedom_duplicate(&$action) {
  // -----------------------------------

    // Get all the params      
  $dirid=GetHttpVars("dirid",10); // where to duplicate
  $docid=GetHttpVars("id",0);       // doc to duplicate
  
  duplicate($action, $dirid, $docid);


  redirect($action,GetHttpVars("app"),"FREEDOM_VIEW&dirid=$dirid");
  
}


?>
