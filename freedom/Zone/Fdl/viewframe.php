<?php
// ---------------------------------------------------------------
// $Id: viewframe.php,v 1.1 2002/07/15 07:03:56 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/viewframe.php,v $
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



// Compute frame values to be inserted in a specific layout
// -----------------------------------
function viewframe(&$action) {
  // -----------------------------------
    
    // GetAllParameters
      $docid = GetHttpVars("id");
  $frameid = GetHttpVars("frameid");
  $abstract = (GetHttpVars("abstract",'N') == "Y");// view doc abstract attributes
    
    
    // Set the globals elements
      
      
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  $doc = new Doc($dbaccess, $docid);
  
  
  $listattr = $doc->GetAttributes(false,true); // get frame attribute also
    
    
    
    
    
  $tval = array();
  while (list($k,$v) = each($listattr)) {
    
    
    if ($v->id == $frameid) $action->lay->set("flabel",$v->labeltext);
    if ($v->frameid != $frameid) continue;
    $value = chop($doc->GetValue($v->id));
    
    if ($value == "") continue;
    //------------------------------
      // Set the table value elements
	if ($v->visibility != "H")	{	
	  // don't see  non abstract if not
	    if (( !$abstract) || ($v->abstract == "Y")) {
	      $tval[$k]["alabel"]=  $v->labeltext;;
	      $tval[$k]["avalue"]=  $doc->GetHtmlValue($v,$value);
	    }
	  
	}
    
    
    
  }
  
  
  $action->lay->setBlockData("FVALUES",$tval);
  if (count($tval) == 0) $action->lay->set("flabel",""); //dont'see frame label is no one value
  
  
  
  
  
  
  
}


?>
