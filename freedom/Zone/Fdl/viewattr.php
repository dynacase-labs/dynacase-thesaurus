<?php
// ---------------------------------------------------------------
// $Id: viewattr.php,v 1.3 2002/08/09 08:47:28 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/viewattr.php,v $
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



// Compute value to be inserted in a specific layout
// -----------------------------------
function viewattr(&$action, $htmlval=true, $htmllink=true) {
  // -----------------------------------
  // GetAllParameters
  $docid = GetHttpVars("id");
  $abstract = (GetHttpVars("abstract",'N') == "Y");// view doc abstract attributes
  

  // Set the globals elements


  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc = new Doc($dbaccess, $docid);

  
  $listattr = $doc->GetAttributes(false,true);
    
    

  // each value can be instanced with L_<ATTRID> for label text and V_<ATTRID> for value

  while (list($k,$v) = each($listattr)) {

   
    $value = chop($doc->GetValue($v->id));

    //------------------------------
    // Set the table value elements
      
    if ($v->visibility != "H")	{	
      // don't see  non abstract if not
      if (($abstract) && ($v->abstract != "Y")) {
	$action->lay->Set("V_".$v->id,"");
	$action->lay->Set("L_".$v->id,"");
      } else {
	$action->lay->Set("V_".$v->id,$htmlval?$doc->GetHtmlValue($v,$value,"_self",$htmllink):$value);
	$action->lay->Set("L_".$v->id,$v->labeltext);
      }
  
    }


  }

  




}


?>
