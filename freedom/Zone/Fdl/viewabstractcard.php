<?php
// ---------------------------------------------------------------
// $Id: viewabstractcard.php,v 1.1 2002/11/04 17:58:08 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/Attic/viewabstractcard.php,v $
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


// -----------------------------------
// -----------------------------------
function viewabstractcard(&$doc) {
  // -----------------------------------
  global $action;

  if (! ereg("([A-Z]*):(.*)", $doc->defaultabstract, $reg)) 
    $action->exitError(sprintf(_("error in pzone format %s"),$doc->pzone["defaultabstract"]));

  
  $lay = new Layout($reg[1]."/Layout/".strtolower($reg[2]).".xml", $action);

  $target="finfo";
  $ulink=true;



  $listattr = $doc->GetAbstractAttributes();
 
  $tableframe=array();
 
  while (list($i,$attr) = each($listattr)) {
  

    //------------------------------
    // Compute value elements
	  
    $value = chop($doc->GetValue($i));

    


      
      if (($value != "") && ($listattr[$i]->visibility != "H"))   {
		
	// print values
	$tableframe[]=array("name"=>$attr->labelText,
			    "value"=>$doc->GetHtmlValue($listattr[$i],$value,$target,$ulink));
	

	      
      
    }
  }




  $lay->SetBlockData("TABLEVALUE",$tableframe);
  

  return $lay->gen();


}


?>
