<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: generic_changecatg.php,v 1.8 2005/06/28 08:37:46 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: generic_changecatg.php,v 1.8 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_changecatg.php,v $
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


include_once("FDL/modcard.php");

include_once("FDL/Class.Dir.php");
include_once("GENERIC/generic_util.php");


// -----------------------------------
function generic_changecatg(&$action) {
  // -----------------------------------

  // special for onefam application
  // Get all the params      
   $dirids=GetHttpVars("dirid", getDefFld($action));
   $ndirids=GetHttpVars("ndirid"); // catg to deleted
   $docid=GetHttpVars("docid"); // the user to change catg




   $dbaccess = $action->GetParam("FREEDOM_DB");

   if (is_array($dirids)) {
     while (list($k,$dirid) = each($dirids)) {	
       $fld = new_Doc($dbaccess, $dirid);
       $fld->AddFile($docid);
     }
   }
   if (is_array($ndirids)) {
     while (list($k,$dirid) = each($ndirids)) {	
       $fld = new_Doc($dbaccess, $dirid);
       $err = $fld->DelFile($docid);

     }
   }
      
  

  

   redirect($action,"FDL","FDL_CARD&id=$docid");
  
}


?>
