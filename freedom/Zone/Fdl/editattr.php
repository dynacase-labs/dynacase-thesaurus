<?php
// ---------------------------------------------------------------
// $Id: editattr.php,v 1.4 2002/11/04 09:13:17 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/editattr.php,v $
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

include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");
include_once("FDL/Class.DocValue.php");

include_once("FDL/freedom_util.php");
include_once("FDL/editutil.php");



// Compute value to be inserted in a specific layout
// -----------------------------------
function editattr(&$action) {
  // -----------------------------------

  // GetAllParameters
  $docid = GetHttpVars("id",0);
  $classid = GetHttpVars("classid");
  

  // Set the globals elements


  $dbaccess = $action->GetParam("FREEDOM_DB");

  if ($docid == 0) $doc = new Doc($dbaccess, $classid);
  else $doc = new Doc($dbaccess, $docid);

  
  $listattr = $doc->GetAttributes();
    
    

  // each value can be instanced with L_<ATTRID> for label text and V_<ATTRID> for value

    while (list($k,$v) = each($listattr)) {


	//------------------------------
	  // Set the table value elements
	    $value = chop($doc->GetValue($v->id));
	
	
	
	$action->lay->Set("V_".$v->id,
			  getHtmlInput($doc,
				       $v, 
				       $value));
      
      $action->lay->Set("L_".$v->id,$v->labeltext);
      
      
    }
  
  
}


?>
