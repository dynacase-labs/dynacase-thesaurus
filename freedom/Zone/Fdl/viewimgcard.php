<?php
// ---------------------------------------------------------------
// $Id: viewimgcard.php,v 1.1 2002/06/10 16:38:59 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/Attic/viewimgcard.php,v $
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
include_once("VAULT/Class.VaultFile.php");

// -----------------------------------
// -----------------------------------
function viewimgcard(&$action) {
  // -----------------------------------

  // GetAllParameters
  $docid = GetHttpVars("id");
  $abstract = (GetHttpVars("abstract",'N') == "Y");// view doc abstract attributes
  $props = (GetHttpVars("props",'Y') == "Y"); // view doc properties


  // Set the globals elements

  $baseurl=$action->GetParam("CORE_BASEURL");
  $standurl=$action->GetParam("CORE_STANDURL");
  $dbaccess = $action->GetParam("FREEDOM_DB");



  

  $doc = new Doc($dbaccess, $docid);



  



  

  if ($props) {
    $action->lay->SetBlockData("PROP",array(array("boo"=>1)));
  }
  if ($abstract){
    // only 3 properties for abstract mode
    $listattr = $doc->GetAbstractAttributes();
  } else {
    $listattr = $doc->GetAttributes();
    
  }
    

  $nattr = count($listattr); // attributes list count




  $nbimg=0;// number of image




  $tableimage=array();
  $vf = new VaultFile($dbaccess, "FREEDOM");

  // view all (and only) images
  for ($i=0; $i < $nattr ; $i++) {

   
    $value = chop($doc->GetValue($listattr[$i]->id));

    //------------------------------
    // Set the table value elements
      
    if (($value != "") && ($listattr[$i]->visibility != "H"))	{
		

      // print values
      switch ($listattr[$i]->type)   {
	      
      case "image": 
		  
	$tableimage[$nbimg]["imgsrc"]=$doc->GetHtmlValue($listattr[$i],$value);
      if (ereg ("(.*)\|(.*)", $value, $reg)) {		 
	// reg[1] is mime type
	$tableimage[$nbimg]["type"]=$reg[1];
	if ($vf -> Show ($reg[2], $info) == "") $fname = $info->name;
	else $fname=_("no filename");
	$tableimage[$nbimg]["name"]=$fname;
      }

      break;
		
		
	
		
      }	      
	    
    }
  
  }

  // Out


  $action->lay->SetBlockData("TABLEIMG",	 $tableimage);

  




}


?>
