<?php
// ---------------------------------------------------------------
// $Id: edit_search.php,v 1.1 2002/02/05 16:34:07 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/edit_search.php,v $
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
// $Log: edit_search.php,v $
// Revision 1.1  2002/02/05 16:34:07  eric
// decoupage pour FREEDOM-LIB
//
// Revision 1.1  2001/11/22 17:49:13  eric
// search doc
//
//
// ---------------------------------------------------------------






// -----------------------------------
function edit_search(&$action) {
  // -----------------------------------

  

  // Get all the params      
  $dir=GetHttpVars("dirid"); // insert search in this folder
  
  $action->lay->Set("dirid", $dir);
  
  
}


?>